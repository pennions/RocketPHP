<?php

require_once 'engine.php';

class RocketLoop
{
    public static function resolveLoop(string $template, $object)
    {
        $resolvedLoopTemplate = $template;
        $allLoopTemplates = RocketEngine::getTemplates($template, "{{%", "%}}");
        $allTopLevelTemplates = RocketEngine::getToplevelTemplates($allLoopTemplates);

        foreach ($allTopLevelTemplates as $loopTemplate) {
            $loopParameters = RocketEngine::getLoopLogic($loopTemplate);

            $mainProp = $loopParameters->property;
            $imaginaryProp = $loopParameters->imaginaryProp;
            $templateToRepeat = $loopParameters->templateToRepeat;
            $item = RocketEngine::getPropertyValue($mainProp, $object);

            if (!$item) {
                continue;
            }

            $replacement = '';


            foreach ($item as $index => $item) {
                $replacement .= self::replacePropWithTrail(
                    $templateToRepeat,
                    $imaginaryProp,
                    is_numeric($index) ? "$mainProp.$index" : $mainProp /** if it is not numeric, we have the trail already. */
                );
            }
            $resolvedLoopTemplate = str_replace($loopTemplate, $replacement, $resolvedLoopTemplate);
        }

        /** Check if we have nested loops. */
        if (str_contains($resolvedLoopTemplate, '{{%')) {
            $resolvedLoopTemplate = self::resolveLoop($resolvedLoopTemplate, $object);
        }

        return $resolvedLoopTemplate;
    }

    /** Create a property trail that can be interpolated */
    private static function replacePropWithTrail($template, $property, $trail)
    {
        $replacedTemplate = preg_replace_callback('/\{\{(.+)\}\}/m', function ($match) use ($property, $trail) {
            return preg_replace("/$property/", $trail, $match[0]);
        }, $template);

        /** also replace the properties of an if within a loop, to match up correctly */
        $replacedTemplate = preg_replace_callback('/if([\s\S]+?)\s|is|not/', function ($match) use ($property, $trail) {
            return preg_replace("/$property/", $trail, $match[0]);
        }, $replacedTemplate);

        /** do the same for nested loops */
        $replacedTemplate = preg_replace_callback('/(of|in)([\s\S]+?)(?=<|{)/', function ($match) use ($property, $trail) {
            return preg_replace("/$property/", $trail, $match[0]);
        }, $replacedTemplate);


        return $replacedTemplate;
    }
}