<?php

use PHPUnit\Framework\TestCase;

require_once 'TestTools.php';
require_once __DIR__ . '/../../../Rocket/functions/conditional.php';

final class ConditionalTest extends TestCase
{

    /**
     * @testdox should  return a template if truthyCheck is true
     */
    public function testReturnTemplateIfTruthy()
    {
        $template = "{{~ if item is I exist  <p>{{item}}</p> ~}}";
        $templateObject = array('item' => 'I exist');
        $result = RocketConditional::resolveConditional($template, $templateObject);

        $this->assertEquals("<p>{{item}}</p>", $result);
    }

    /**
     * @testdox it should return the template, even though it is a normal text due to \n
     */
    public function testReturnTemplateIfTruthyAndWithAnEnter()
    {
        $template = "{{~ if item is I exist 
            item
        ~}}";
        $templateObject = array('item' => 'I exist');
        $result = RocketConditional::resolveConditional($template, $templateObject);

        $this->assertEquals("item", $result);
    }
    /**
     * @testdox should return a template if property exists
     */
    public function testReturnTemplateIfPropertyExists()
    {
        $template = "{{~ if item <p>{{item}}</p> ~}}";
        $templateObject = array('item' => 'I exist');
        $result = RocketConditional::resolveConditional($template, $templateObject);

        $this->assertEquals("<p>{{item}}</p>", $result);
    }
    /**
     * @testdox should not return a template if truthyCheck is false
     */
    public function testNoTemplateWhenFalsy()
    {
        $template = "{{~ if item is true <p>{{item}}</p> ~}}";
        $templateObject = array('item' => false);
        $result = RocketConditional::resolveConditional($template, $templateObject);

        $this->assertEquals("", $result);
    }
    /**
     * @testdox should return a template if falsyCheck is true
     */
    public function testNotTruthyCheckReturnsTemplate()
    {
        $template = "{{~ if item not true <p>{{item}}</p> ~}}";
        $templateObject = array('item' => false);
        $result = RocketConditional::resolveConditional($template, $templateObject);

        $this->assertEquals("<p>{{item}}</p>", $result);
    }


    /**
     * @testdox should return the same template if an if statement is not found
     */
    public function testReturnTemplateIfNoIfFound()
    {
        $template = "<p>{{item}}</p>";
        $templateObject = array('item' => false);
        $result = RocketConditional::resolveConditional($template, $templateObject);

        $this->assertEquals("<p>{{item}}</p>", $result);

    }
    /**
     * @testdox returns the nested if when conditional is true
     */
    public function testReturnNestedTemplateIfTruthy()
    {
        $template = "{{~ if item <h1>Some item:</h1>  {{~ if item.label <p>{{item.label}}</p> ~}} ~}}";
        $templateObject = array('item' => array('label' => 'Nested is tested'));
        $result = RocketConditional::resolveConditional($template, $templateObject);

        $this->assertEquals("<h1>Some item:</h1>  <p>{{item.label}}</p>", $result);
    }
    /**
     * @testdox can resolve a property trail
     */
    public function testPropertyTrail()
    {
        $template = "{{~ if item.label <p>{{item.label}}</p> ~}}";
        $templateObject = array('item' => array('label' => 'Trail is tested'));
        $result = RocketConditional::resolveConditional($template, $templateObject);

        $this->assertEquals("<p>{{item.label}}</p>", $result);
    }
    /**
     * @testdox can resolve a property trail that contains an is
     */
    public function testPropertyTrailWithAnIs()
    {
        $template = '{{~ if shoppingList.bakery.birthday is carrot cake 
            <div>The cake is not a lie!</div>
        ~}}';

        $templateObject = array('shoppingList' => array('bakery' => array('birthday' => 'carrot cake', 'daily' => ['Bread', 'Cookies'])));
        $result = RocketConditional::resolveConditional($template, $templateObject);

        $this->assertEquals("<div>The cake is not a lie!</div>", TestTools::cleanHtml($result));
    }

    /**
     * @testdox can resolve even when there us a not inside the html without a not in the first line.
     */
    public function testEmptyArrayInIf()
    {
        $template = '{{~ if shoppingList.bakery
            <div>Do not render</div>
        ~}}';

        $templateObject = array('shoppingList' => array('bakery' => array()));
        $result = RocketConditional::resolveConditional($template, $templateObject);

        $this->assertEquals("", TestTools::cleanHtml($result));
    }


    /**
     * @testdox can resolve multiple conditionals
     */
    public function testDoubleConditional()
    {
        $template = '{{~ if top
            <div>Render me! </div>

            {{~ if bottom <span>me too</span> ~}}
        ~}}';

        $templateObject = array('top' => true, 'bottom' => true);
        $result = RocketConditional::resolveConditional($template, $templateObject);

        $this->assertEquals("<div>Render me! </div><span>me too</span>", TestTools::cleanHtml($result));
    }
}