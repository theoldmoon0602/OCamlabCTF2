<?php

/* base.html */
class __TwigTemplate_831ff0d552db7bf644a4d3d900756433712e01c0aee999634128118e2662a3bb extends Twig_Template
{
    private $source;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html>
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1\">
  <link rel=\"shortcut icon\" href=\"favicon.png\">
  <link rel=\"stylesheet\" href=\"style.css\">
  <title>OCamlab CTF #2</title>
</head>
<body>
  <div id=\"app\">
    <header>
      <nav class=\"navbar\">
        <span> OCamlab CTF #2</span>
        <ul>
          <li><a href=\"#\">TOP</a></li>
          <li><a href=\"#\">Users</a></li>
          <li><a href=\"#\">Rules</a></li>
          <li><a href=\"#\">Challenges</a></li>
          <li><a href=\"#\">Scores</a></li>

          <li class=\"navbar-right\"><a href=\"#\">Login</a></li>
        </ul>
      </nav>
    </header>

    <main>
    ";
        // line 28
        $this->displayBlock('content', $context, $blocks);
        // line 29
        echo "    </main>

  </div>
</body>
</html>
";
    }

    // line 28
    public function block_content($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "base.html";
    }

    public function getDebugInfo()
    {
        return array (  64 => 28,  55 => 29,  53 => 28,  24 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "base.html", "/home/theoldmoon0602/code/web/OCamlabCTF2/templates/base.html");
    }
}
