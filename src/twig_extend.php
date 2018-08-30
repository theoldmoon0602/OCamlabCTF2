<?php


$function = new Twig_SimpleFunction('csrf_tokens', function () {
  $nameKey = $this->csrf->getTokenNameKey();
  $valueKey = $this->csrf->getTokenValueKey();
  $name = $request->getAttribute($nameKey);
  $value = $request->getAttribute($valueKey);

  return "<input type='hidden' name='$nameKey' value='$name'><input type='hidden' name='$valueKey' value='$value'>";
});

$container->get('view')->getEnvironment()->addFunction($function);
