<?php

class TestTools
{
    const CLEAN_HTML_REGEX = '/[>](\s+)[<]/m';

    public static function cleanHtml($template)
    {
        $template = trim($template);
        $template = preg_replace(self::CLEAN_HTML_REGEX, '><', $template);
        $template = str_replace("\n", '', $template);
        $template = str_replace("\r", '', $template);
        $template = preg_replace("/\s{2,}/", "", $template);

        return $template;
    }
}

?>