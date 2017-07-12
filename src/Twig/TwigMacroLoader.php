<?php

namespace Bjorvack\AtomicBuilder\Twig;

use Twig_Error;
use Twig_Extension;
use Twig_SimpleFunction;

class TwigMacroLoader extends Twig_Extension
{
    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getFunctions()
    {
        return array(
            // "*"" is used to get "template_macro" as $macro as third argument
            new Twig_SimpleFunction('macro_*', array($this, 'getMacro'), array(
                'needs_environment' => true, // $env first argument will render the macro
                'needs_context' => true,     // $context second argument an array of view vars
                'is_safe' => array('html'),  // function returns escaped html
                'is_variadic' => true,       // and takes any number of arguments
            ))
        );
    }

    public function getMacro(\Twig_Environment $env, array $context, $macro, array $vars = array())
    {
        list($name, $func) = explode('_', $macro);

        $notInContext = 0; // helps generate unique context key

        $varToContextKey = function ($var) use (&$context, $name, $func, &$notInContext) {
            if (false !== $idx = array_search($var, $context, true)) {
                return $idx;
            }

            // else the var does not belong to context
            $key = '_'.$name.'_'.$func.'_'.++$notInContext;
            $context[$key] = $var;

            return $key;
        };

        $args = implode(', ', array_map($varToContextKey, $vars));

        $twig = <<<EOT
{% import '$name.php.twig' as $name %}
{{ $name.$func($args) }}
EOT;

        try {
            $html = $env->createTemplate($twig)->render($context);
        } catch (Twig_Error $e) {
            //$e->setTemplateFile(sprintf($twig->path. '/%s.twig', $name));

            throw $e;
        }

        return $html;
    }

    public function getName()
    {
        return 'macro_autoload_extension';
    }
}