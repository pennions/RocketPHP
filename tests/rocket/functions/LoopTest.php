<?php

use PHPUnit\Framework\TestCase;

require_once 'TestTools.php';
require_once __DIR__ . '/../../../Rocket/functions/loop.php';

final class LoopTest extends TestCase
{
    /**
     * @testdox it adds a trail to the values from an array property in the given object in the template
     */
    public function testBasicLoop()
    {
        $template = "<ul>{{% for prop of item <li>{{prop}}</li> %}}</ul>";

        $templateObject = [
            "item" => ["Item1", "Item2", "Item3"],
        ];

        $result = RocketLoop::resolveLoop($template, $templateObject);

        $this->assertEquals("<ul><li>{{item.0}}</li><li>{{item.1}}</li><li>{{item.2}}</li></ul>", $result);
    }

    /**
     * @testdox it adds a trail to the values from an array property in the given object in the template even if they are nested.
     */
    public function testLoopWithConditional()
    {
        $template = "<ul>{{% for prop of item  {{~ if prop <li>{{prop}}</li> ~}} %}}</ul>";

        $templateObject = [
            "item" => ["Item1", "Item2", "Item3"],
        ];

        $result = RocketLoop::resolveLoop($template, $templateObject);

        $this->assertEquals("<ul>{{~ if item.0 <li>{{item.0}}</li> ~}}{{~ if item.1 <li>{{item.1}}</li> ~}}{{~ if item.2 <li>{{item.2}}</li> ~}}</ul>", $result);
    }

    /**
     * @testdox it also works with for ... in ...
     */
    public function testBasicLoopWithForInSyntax()
    {
        $template = "<ul>{{% for prop in item <li>{{prop}}</li> %}}</ul>";

        $templateObject = [
            "item" => ["Item1", "Item2", "Item3"],
        ];

        $result = RocketLoop::resolveLoop($template, $templateObject);

        $this->assertEquals("<ul><li>{{item.0}}</li><li>{{item.1}}</li><li>{{item.2}}</li></ul>", $result);
    }

    /**
     * @testdox it adds a trail to the values from an object in an array property in the given object in the template
     */
    public function testAddingTrailOfPropInArrayWithObjects()
    {
        $template = "<ul>{{% for object in item <li>{{object.label}}</li> %}}</ul>";

        $templateObject = [
            "item" => [["label" => "Item1"], ["label" => "Item2"], ["label" => "Item3"]],
        ];

        $result = RocketLoop::resolveLoop($template, $templateObject);
        $this->assertEquals("<ul><li>{{item.0.label}}</li><li>{{item.1.label}}</li><li>{{item.2.label}}</li></ul>", $result);
    }

    /**
     * @testdox it resolves nested loop
     */
    public function testNestedLoop()
    {
        $template = "<div>TestDiv</div>{{% for item of list <ul>{{% for object of item <li>{{object.label}}</li> %}}</ul>
        %}}";

        $templateObject = [
            "list" => [[["label" => "Item1"], ["label" => "Item2"], ["label" => "Item3"]]],
        ];

        $result = RocketLoop::resolveLoop($template, $templateObject);
        $this->assertEquals("<div>TestDiv</div><ul><li>{{list.0.0.label}}</li><li>{{list.0.1.label}}</li><li>{{list.0.2.label}}</li></ul>", TestTools::cleanHtml($result));
    }

    /**
     * @testdox it resolves a loop from a nested property
     */
    public function testLoopFromNestedProperty()
    {
        $template = "<ul>{{% for item of object.list <li>{{ item.label }}</li> %}}</ul>";

        $templateObject = [
            "object" => [
                "list" => [["label" => "Item1"], ["label" => "Item2"], ["label" => "Item3"]],
            ]
        ];

        $result = RocketLoop::resolveLoop($template, $templateObject);
        $this->assertEquals("<ul><li>{{ object.list.0.label }}</li><li>{{ object.list.1.label }}</li><li>{{ object.list.2.label }}</li></ul>", TestTools::cleanHtml($result));
    }
}