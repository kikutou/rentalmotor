<?php

/* Block/free.twig */
class __TwigTemplate_8991d56ff754be795115327b2bd9efd9fdcec744a95d689b69e00985754a10c0 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 22
        echo "<div class=\"col-sm-3 txt_bnr_area\">
    <div class=\"txt_bnr\">";
        // line 24
        if ($this->getAttribute(($context["BaseInfo"] ?? null), "delivery_free_amount", array())) {
            // line 25
            echo "<strong>";
            echo twig_escape_filter($this->env, twig_number_format_filter($this->env, $this->getAttribute(($context["BaseInfo"] ?? null), "delivery_free_amount", array())), "html", null, true);
            echo "円以上の購入で<br><strong>配送料無料</strong></strong><br>一部地域は除く";
        } else {
            // line 27
            echo "<strong>0円以上の購入で<br><strong>配送料無料</strong></strong><br>一部地域は除く";
        }
        // line 29
        echo "</div>
</div>
";
    }

    public function getTemplateName()
    {
        return "Block/free.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  32 => 29,  29 => 27,  24 => 25,  22 => 24,  19 => 22,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "Block/free.twig", "/var/www/html/liv-motor/src/Eccube/Resource/template/default/Block/free.twig");
    }
}
