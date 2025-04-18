<?php

require_once __DIR__ . '/functions/partial.php';
require_once __DIR__ . '/functions/loop.php';
require_once __DIR__ . '/functions/conditional.php';
require_once __DIR__ . '/functions/wrapper.php';
require_once __DIR__ . '/functions/interpolation.php';

class Rocket
{
    /** Constructs the template and makes it ready for interpolation */
    public static function buildTemplate($template, $object)
    {
        $newTemplate = RocketPartial::resolvePartials($template, $object);
        $newTemplate = RocketWrapper::resolveWrapper($newTemplate);
        $newTemplate = RocketLoop::resolveLoop($newTemplate, $object);
        $newTemplate = RocketConditional::resolveConditional($newTemplate, $object);
        return $newTemplate;
    }

    public static function interpolateTemplate($compiledTemplate, $object)
    {
        return RocketInterpolation::interpolateTemplate($compiledTemplate, $object);
    }

    public static function render($template, $object)
    {
        $compiledTemplate = Rocket::buildTemplate($template, $object);
        return RocketInterpolation::interpolateTemplate($compiledTemplate, $object);
    }
}