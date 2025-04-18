<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../Rocket/functions/interpolation.php';

final class InterpolationTest extends TestCase
{
    /**
     * @testdox it adds values from a given object in the right places
     */
    public function testInterpolation()
    {
        $template = "<div>{{ variableB }} {{variableC}} {{variableA}}</div>";

        $templateObject = array(
            'variableA' => 'works.',
            'variableB' => 'Great!',
            'variableC' => 'Interpolation'
        );

        $result = RocketInterpolation::interpolateTemplate($template, $templateObject);
        $this->assertEquals("<div>Great! Interpolation works.</div>", $result);
    }

    /**
     * @testdox it can add nested values
     */
    public function testNestedInterpolation()
    {
        $template = "<div>{{ variableB }} {{variableC.a}} {{variableA}}</div>";

        $templateObject = array(
            'variableA' => 'works.',
            'variableB' => 'Great!',
            'variableC' => array('a' => 'Nested interpolation')
        );
        $result = RocketInterpolation::interpolateTemplate($template, $templateObject);
        $this->assertEquals("<div>Great! Nested interpolation works.</div>", $result);
    }

    /**
     * @testdox it can escape html.
     */
    public function testEscapedInterpolation()
    {
        $template = "<pre><code>{{! myExample }}</code></pre>";

        $templateObject = array(
            'myExample' => '<p>This HTML is for a code example</p>',
        );

        $result = RocketInterpolation::interpolateTemplate($template, $templateObject);
        $this->assertEquals("<pre><code>&lt;p&gt;This HTML is for a code example&lt;&#039;p&gt;</code></pre>", $result);
    }
}
