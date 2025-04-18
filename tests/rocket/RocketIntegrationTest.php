<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/functions/TestTools.php';
require_once __DIR__ . '/../../rocket/rocket.php';

final class RocketIntegrationTest extends TestCase
{
    /**
     * @testdox it correctly renders an array
     */
    public function testRenderingAnArray()
    {
        $template = "<ul>{{% for prop of items <li>{{prop}}</li> %}}</ul>";
        $templateObject = [
            "items" => ["Item1", "Item2", "Item3"],
        ];

        $result = Rocket::render($template, $templateObject);

        $this->assertEquals("<ul><li>Item1</li><li>Item2</li><li>Item3</li></ul>", TestTools::cleanHtml($result));
    }

    /**
     * @testdox it correctly renders an array with objects
     */
    public function testRenderingArrayWithObjects()
    {
        $template = "<ul>{{% for object of items <li>{{object.label}}</li> %}}</ul>";
        $templateObject = [
            "items" => [["label" => "Item1"], ["label" => "Item2"], ["label" => "Item3"]],
        ];

        $result = Rocket::render($template, $templateObject);

        $this->assertEquals("<ul><li>Item1</li><li>Item2</li><li>Item3</li></ul>", TestTools::cleanHtml($result));
    }

    /**
     * @testdox it renders a nested loop correctly
     */
    public function testRenderingNestedLoop()
    {
        $template = "<div>TestDiv</div>{{% for items of list <ul>{{% for object of items <li>{{object.label}}</li> %}}</ul> %}}";
        $templateObject = [
            "list" => [["label" => "Item1"], ["label" => "Item2"], ["label" => "Item3"]],
        ];

        $result = Rocket::render($template, $templateObject);

        $this->assertEquals("<div>TestDiv</div><ul><li>Item1</li></ul><ul><li>Item2</li></ul><ul><li>Item3</li></ul>", TestTools::cleanHtml($result));
    }

    /**
     * @testdox it renders a nested loop correctly if it is multi-line
     */
    public function testMultilineNestedLoop()
    {

        $template = '
<div>TestDiv</div>
{{% for item of list
<ul>
    {{% for object of item
    <li>{{object.label}}</li>
    %}}
</ul>
%}}';

        $templateObject = [
            "list" => [["label" => "Item1"], ["label" => "Item2"], ["label" => "Item3"]],
        ];

        $result = Rocket::render($template, $templateObject);

        $this->assertEquals("<div>TestDiv</div><ul><li>Item1</li></ul><ul><li>Item2</li></ul><ul><li>Item3</li></ul>", TestTools::cleanHtml($result));
    }

    /**
     * @testdox it renders a nested conditional correctly
     */
    public function testRenderingNestedConditional()
    {
        $template = "{{~ if item <h1>Some item:</h1>{{~ if item.label <p>{{item.label}}</p> ~}} ~}}";
        $templateObject = [
            "item" => ["label" => "Nested is tested"],
        ];

        $result = Rocket::render($template, $templateObject);

        $this->assertEquals("<h1>Some item:</h1><p>Nested is tested</p>", TestTools::cleanHtml($result));
    }

    /**
     * @testdox it renders a nested multiline conditional correctly
     */
    public function testRenderingNestedMultilineConditional()
    {
        $template = "
{{~ if item
    <h1>Some item:</h1>
    {{~ if item.label
        <p>{{item.label}}</p>
    ~}}
~}}";

        $templateObject = [
            "item" => ["label" => "Nested is tested"],
        ];

        $result = Rocket::render($template, $templateObject);

        $this->assertEquals("<h1>Some item:</h1><p>Nested is tested</p>", TestTools::cleanHtml($result));
    }

    /**
     * @testdox it renders a loop from a nested list
     */
    public function testRenderingLoopFromNestedList()
    {
        $template = "<ul>{{% for item of object.list <li>{{ item.label }}</li> %}}</ul>";
        $templateObject = [
            "object" => [
                "list" => [["label" => "Item1"], ["label" => "Item2"], ["label" => "Item3"]],
            ]
        ];

        $result = Rocket::render($template, $templateObject);

        $this->assertEquals("<ul><li>Item1</li><li>Item2</li><li>Item3</li></ul>", TestTools::cleanHtml($result));
    }

    /**
     * @testdox it can render a view with partials
     */
    public function testRenderingPartials()
    {
        $template = "
        {{# partials.username #}}
        {{# partials.shoppingList #}}
";
        $templateObject = [
            "shoppingList" => [
                "groceryStore" => ["Carrot", "Melon", "Potato"],
            ],
            "username" => "Rocket",
            "partials" => [
                "shoppingList" => "<ul> {{% for item in shoppingList.groceryStore <li> {{ item }} </li> %}} </ul>",
                "username" => "{{~ if username <div>{{ username }}</div> ~}} {{# partials.nested_partial #}}",
                "nested_partial" => "<span>Nested is also tested!</span>"
            ]
        ];

        $result = Rocket::render($template, $templateObject);

        $this->assertEquals("<div>Rocket</div><span>Nested is also tested!</span><ul><li> Carrot </li><li> Melon </li><li> Potato </li></ul>", TestTools::cleanHtml($result));
    }
}