<?php

/* CrisoCollaThemeBundle:Default:index.html.twig */
class __TwigTemplate_6a4d0942c940eecf88bcfe5adb0301b1 extends Twig_Template
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
        // line 1
        echo "Theme -- <br>

";
        // line 3
        echo twig_escape_filter($this->env, (isset($context["content"]) ? $context["content"] : $this->getContext($context, "content")), "html", null, true);
        echo " 

<br>

-- Theme
";
    }

    public function getTemplateName()
    {
        return "CrisoCollaThemeBundle:Default:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  23 => 3,  19 => 1,);
    }
}
