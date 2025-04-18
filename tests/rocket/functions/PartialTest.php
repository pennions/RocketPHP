<?php

use PHPUnit\Framework\TestCase;

require_once 'TestTools.php';
require_once __DIR__ . '/../../../Rocket/functions/partial.php';

final class PartialTest extends TestCase
{
    /**
     * @testdox it can add partials
     */
    public function testAddPartials()
    {
        $template = "
        <body>
            {{# partials.navbar #}}
            <main>Main content and such</main>
            {{# partials.footer #}}
        </body>";

        $templateObject = array(
            'partials' => [
                "navbar" => "<nav><a>Some menu content</a>{{~ admin <button>To admin panel</button> ~}}</nav>",
                "footer" => "<footer>(C) made with Rocket</footer>"
            ]
        );

        $result = RocketPartial::resolvePartials($template, $templateObject);
        $this->assertEquals("<body><nav><a>Some menu content</a>{{~ admin <button>To admin panel</button> ~}}</nav><main>Main content and such</main><footer>(C) made with Rocket</footer></body>", TestTools::cleanHtml($result));
    }

    /**
     * @testdox it can resolve partials beginning with template characters
     */
    public function testResolvePartialsWithTemplateCharacters()
    {
        $template = "
        {{# partials.username #}}
        {{# partials.shoppingList #}}";

        $templateObject = array(
            'partials' => [
                "shoppingList" => "<ul> {{% for item in shoppingList.groceryStore <li> {{ item }} </li> %}} </ul>",
                "username" => "{{~ if username <div>{{ username }}</div> ~}}"
            ]
        );

        $result = RocketPartial::resolvePartials($template, $templateObject);
        $this->assertEquals("{{~ if username <div>{{ username }}</div> ~}}<ul> {{% for item in shoppingList.groceryStore <li> {{ item }} </li> %}} </ul>", TestTools::cleanHtml($result));
    }

    /**
     * @testdox it can resolve nested partials
     */
    public function testResolveNestedPartials()
    {
        $template = "
        {{# partials.lists #}}
        ";
   
        $templateObject = array(
            'partials' => [
                "shoppingList" => "<ul> {{% for item in shoppingList.groceryStore <li> {{ item }} </li> %}} </ul>",
                "lists" => "{{# partials.shoppingList #}}"
            ]
        );

        $result = RocketPartial::resolvePartials($template, $templateObject);
        $this->assertEquals("<ul> {{% for item in shoppingList.groceryStore <li> {{ item }} </li> %}} </ul>", TestTools::cleanHtml($result));
    }
}