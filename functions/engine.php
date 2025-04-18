<?php

class RocketEngine
{
    /** 
     * templateStart and templateEnd must be the same length.
     */
    public static function getTemplates($template, $templateStart, $templateEnd)
    {
        $templateLength = strlen($template);

        $startSequenceResult = self::createSequenceResultClass();
        $endSequenceResult = self::createSequenceResultClass();

        $startResultcounter = 0;
        $endResultcounter = 0;
        $startIndexes = [];
        $endIndexes = [];

        $templateSets = [];

        for ($charIndex = 0; $charIndex < $templateLength; $charIndex++) {

            $startSequenceResult = self::isCharacterSequence($template[$charIndex], $templateStart, $startSequenceResult);
            $endSequenceResult = self::isCharacterSequence($template[$charIndex], $templateEnd, $endSequenceResult);

            if ($startSequenceResult->match) {
                $startResultcounter = $startResultcounter + 1;
                $startIndexes[] = $charIndex - (strlen($templateStart) - 1);
            }

            if ($endSequenceResult->match) {
                $endResultcounter = $endResultcounter + 1;
                $endIndexes[] = $charIndex + 1;
            }

            /** we found a matching set or multiple nested sets. so now we can backtrack. */
            if ($startResultcounter > 0 && $endResultcounter > 0 && $startResultcounter === $endResultcounter) {
                $numberOfSets = count($startIndexes);

                for ($setIndex = 0; $setIndex < $numberOfSets; $setIndex++) {
                    $startResultIndex = $startIndexes[$setIndex];

                    for ($endIndex = 0; $endIndex < $numberOfSets; $endIndex++) {
                        $endResultIndex = $endIndexes[$endIndex];

                        /** we are matching against a previous end. */
                        if ($endResultIndex < $startResultIndex) {
                            continue;
                        }

                        $possibleTemplate = substr($template, $startResultIndex, $endResultIndex - $startResultIndex);

                        /** verify that inside either the starts and ends are equal (or 0, but that is also equal) */
                        $numberOfStarts = substr_count($possibleTemplate, $templateStart);
                        $numberOfEnds = substr_count($possibleTemplate, $templateEnd);

                        /** The template is complete! 
                         * NB. with some formatting we get a ghost empty template, skip those.
                         */
                        if ($numberOfStarts === $numberOfEnds && strlen($possibleTemplate)) {
                            $templateSets[] = $possibleTemplate;
                            break;
                        }
                    }
                }

                /** reset the counter and the indexes */
                $startResultcounter = 0;
                $endResultcounter = 0;
                $startIndexes = [];
                $endIndexes = [];
            }
        }
        return $templateSets;
    }

    public static function getToplevelTemplates(array $templates)
    {
        $toplevelTemplates = [];

        foreach ($templates as $index => $template) {
            $notNested = true;

            /** check if it is in any of the previous, if so, it is nested. */
            for ($templateIndex = 0; $templateIndex < $index; $templateIndex++) {
                if (strpos($templates[$templateIndex], $template)) {
                    $notNested = false;
                    break;
                }
            }

            if ($notNested) {
                $toplevelTemplates[] = $template;
            }
        }

        return $toplevelTemplates;
    }

    /** Get the innerpart of a template. That means without the template syntax, for further processing */
    public static function getInnerTemplate($template, $templateTokenLength = 3)
    {
        $start = $templateTokenLength;

        /** template length minus the 2x template tokens, e.g. ~}} */
        $end = strlen($template) - (2 * $templateTokenLength);

        return trim(substr($template, $start, $end));
    }

    public static function getConditionalLogic($template)
    {
        $conditionalParameters = new stdClass();
        $conditionalParameters->truthy = true;

        $innerTemplate = self::getInnerTemplate($template);
        $logicString = self::getLogicString($innerTemplate);

        $conditionalParameters->logiclessTemplate = trim(substr($innerTemplate, strlen($logicString)));

        /** conditional logic: if [prop] (is|not)? [comparison] */

        /** we can just split it on a space first, 'quick and dirty'. 
         * then we can reconcatenate the comparison. We start after the 'if'
         */

        $logicBits = explode(" ", trim(substr($logicString, 2)));

        $numberOfBits = count($logicBits);

        $conditionalParameters->property = $logicBits[0];
        array_shift($logicBits); /** remove it. */

        if ($numberOfBits > 1) {
            $conditionalParameters->truthy = $logicBits[0] === 'is'; /** because we removed the property */
            array_shift($logicBits); /** remove it. */

            /** capture the rest again. */
            $conditionalParameters->comparison = implode(" ", $logicBits);
        }

        return $conditionalParameters;
    }

    public static function getLoopLogic($template)
    {
        $loopParameters = new stdClass();

        $innerTemplate = self::getInnerTemplate($template);
        $logicString = self::getLogicString($innerTemplate);

        $loopParameters->templateToRepeat = trim(str_replace($logicString, '', $innerTemplate));

        // would be nice to have indexes as well

        /** loop logic: for [imaginativeProp] (in|of)? [property] */
        $loopBits = explode(" ", trim(substr($logicString, 3)));

        $loopParameters->imaginaryProp = $loopBits[0];
        $loopParameters->property = $loopBits[2];

        return $loopParameters;
    }

    public static function getWrapperProperty($template)
    {
        $wrapperParameters = new stdClass();

        $innerTemplate = self::getInnerTemplate($template);
        $logicString = self::getLogicString($innerTemplate);

        /** wrapper logic: from [property] */
        $wrapperParameters->property = trim(substr($logicString, 4));
        $wrapperParameters->templateToFill = trim(str_replace($logicString, '', $innerTemplate));

        return $wrapperParameters;
    }

    public static function getProperty($template)
    {
        $property = '';

        $logicDeterminator = substr($template, 2, 1);

        switch ($logicDeterminator) {
            case '%': {
                    $loopLogic = RocketEngine::getLoopLogic($template);
                    $property = $loopLogic->property;
                    break;
                }
            case '~': {
                    $conditionalLogic = RocketEngine::getConditionalLogic($template);
                    $property = $conditionalLogic->property;
                    break;
                }
            case '#': {
                    $property = trim(RocketEngine::getInnerTemplate($template, 3));
                    break;
                }
            default: {
                    $rawProperty = RocketEngine::getInnerTemplate($template, 2);
                    if ($rawProperty[0] === '!') {
                        $property = trim($rawProperty);
                    } else {
                        $property = $rawProperty;
                    }
                    break;
                }
        }
        return $property;
    }

    public static function getPropertyValue($property, $assocArray)
    {
        $propertyTrail = explode('.', $property);

        $templateItem = '';

        foreach ($propertyTrail as $property) {
            $property = trim($property);
            if (!$templateItem) {
                $templateItem = array_key_exists($property, $assocArray) ? $assocArray[$property] : '';
            } else {
                if (is_array($templateItem) && array_key_exists($property, $templateItem)) {
                    $templateItem = $templateItem[$property];
                } else {
                    $templateItem = null;
                    break;
                }
            }
        }
        return $templateItem;
    }

    private static function getLogicString($innerTemplate)
    {
        $logicString = '';
        $logicEnds = ["\n", "{", "<"];
        $maxLength = strlen($innerTemplate);

        for ($charIndex = 0; $charIndex < $maxLength; $charIndex++) {
            $nextChar = $innerTemplate[$charIndex];

            if (in_array($nextChar, $logicEnds)) {
                return $logicString;
            } else {
                $logicString .= $nextChar;
            }
        }
        return $logicString;
    }

    private static function createSequenceResultClass()
    {
        $result = new stdClass();
        $result->sequence = '';
        $result->match = false;
        return $result;
    }

    /** $nextChar: the next character in the string
     *  $find: the string we need to find
     *  $sequenceResult: the stdclass that has a sequence string to match against and a match boolean,
     */
    private static function isCharacterSequence($nextChar, $find, $sequenceResult)
    {
        $newSequence = $sequenceResult->sequence;
        if (strlen($newSequence) === strlen($find)) {
            /** remove the first character, so we shift to the right */
            $newSequence = substr($newSequence, 1);
        }

        $newSequence .= $nextChar;
        $sequenceResult->sequence = $newSequence;
        $sequenceResult->match = $newSequence === $find;

        return $sequenceResult;
    }
}

?>