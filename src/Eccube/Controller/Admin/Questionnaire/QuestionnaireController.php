<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Controller\Admin\Questionnaire;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\CsvType;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuestionnaireController extends AbstractController
{
    public function index(Application $app, Request $request, $page_no = null)
    {
        $session = $request->getSession();
        $pagination = array();
        $builder = $app['form.factory']
            ->createBuilder('admin_search_questionnaire');

        $event = new EventArgs(
            array(
                'builder' => $builder,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_QUESTIONNAIRE_INDEX_INITIALIZE, $event);

        $searchForm = $builder->getForm();

        //アコーディオンの制御初期化( デフォルトでは閉じる )
        $active = false;

        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.questionnaire.search.page_count', $app['config']['default_page_count']);

        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if ($page_count_param && is_numeric($page_count_param)) {
            foreach ($pageMaxis as $pageMax) {
                if ($page_count_param == $pageMax->getName()) {
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.questionnaire.search.page_count', $page_count);
                    break;
                }
            }
        }

        if ('POST' === $request->getMethod()) {

            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();

                // paginator
                $qb = $app['eccube.repository.questionnaire']->getQueryBuilderBySearchData($searchData);
                $page_no = 1;

                $event = new EventArgs(
                    array(
                        'form' => $searchForm,
                        'qb' => $qb,
                    ),
                    $request
                );
                $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_QUESTIONNAIRE_INDEX_SEARCH, $event);

                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );

                // sessionに検索条件を保持.
                $viewData = \Eccube\Util\FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.questionnaire.search', $viewData);
                $session->set('eccube.admin.questionnaire.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.questionnaire.search');
                $session->remove('eccube.admin.questionnaire.search.page_no');
                $session->remove('eccube.admin.questionnaire.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.questionnaire.search.page_no'));
                } else {
                    $session->set('eccube.admin.questionnaire.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.questionnaire.search');
                if (!is_null($viewData)) {
                    // sessionに保持されている検索条件を復元.
                    $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

                    // 表示件数
                    $page_count = $request->get('page_count', $page_count);

                    $qb = $app['eccube.repository.questionnaire']->getQueryBuilderBySearchData($searchData);

                    $event = new EventArgs(
                        array(
                            'form' => $searchForm,
                            'qb' => $qb,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_QUESTIONNAIRE_INDEX_SEARCH, $event);

                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count
                    );
                }
            }
        }

        return $app->render('Questionnaire/index.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'active' => $active,
        ));
    }

    public function edit(Application $app, Request $request, $id)
    {
        // 編集
        if ($id) {
            $Questionnaire = $app['orm.em']
                ->getRepository('Eccube\Entity\Questionnaire')
                ->find($id);

            if (is_null($Questionnaire)) {
                throw new NotFoundHttpException();
            }

        } else {
            throw new NotFoundHttpException();
        }

        // 会員登録フォーム
        $builder = $app['form.factory']
            ->createBuilder('admin_questionnaire', $Questionnaire);

        $event = new EventArgs(
            array(
                'builder' => $builder,
                'Customer' => $Questionnaire,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_QUESTIONNAIRE_EDIT_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                log_info('アンケート編集開始', array($Questionnaire->getId()));

                $app['orm.em']->persist($Questionnaire);
                $app['orm.em']->flush();

                log_info('アンケート編集完了', array($Questionnaire->getId()));

                $event = new EventArgs(
                    array(
                        'form' => $form,
                        'Questionnaire' => $Questionnaire,
                    ),
                    $request
                );
                $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_QUESTIONNAIRE_EDIT_INDEX_COMPLETE, $event);

                $app->addSuccess('admin.questionnaire.save.complete', 'admin');

                return $app->redirect($app->url('admin_questionnaire_edit', array(
                    'id' => $Questionnaire->getId(),
                )));
            } else {
                $app->addError('admin.questionnaire.save.failed', 'admin');
            }
        }

        return $app->render('Questionnaire/edit.twig', array(
            'form' => $form->createView(),
            'Questionnaire' => $Questionnaire,
        ));
    }

    public function delete(Application $app, Request $request, $id)
    {
//        $this->isTokenValid($app);
//
//        log_info('会員削除開始', array($id));
//
//        $session = $request->getSession();
//        $page_no = intval($session->get('eccube.admin.customer.search.page_no'));
//        $page_no = $page_no ? $page_no : Constant::ENABLED;
//
//        $Customer = $app['orm.em']
//            ->getRepository('Eccube\Entity\Customer')
//            ->find($id);
//
//        if (!$Customer) {
//            $app->deleteMessage();
//            return $app->redirect($app->url('admin_customer_page', array('page_no' => $page_no)).'?resume='.Constant::ENABLED);
//        }
//
//        $Customer->setDelFlg(Constant::ENABLED);
//        $app['orm.em']->persist($Customer);
//        $app['orm.em']->flush();
//
//        log_info('会員削除完了', array($id));
//
//        $event = new EventArgs(
//            array(
//                'Customer' => $Customer,
//            ),
//            $request
//        );
//        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_CUSTOMER_DELETE_COMPLETE, $event);
//
//        $app->addSuccess('admin.customer.delete.complete', 'admin');
//
//        return $app->redirect($app->url('admin_customer_page', array('page_no' => $page_no)).'?resume='.Constant::ENABLED);
    }
}
