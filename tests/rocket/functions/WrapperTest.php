<?php

use PHPUnit\Framework\TestCase;

require_once 'TestTools.php';
require_once __DIR__ . '/../../../Rocket/functions/wrapper.php';

final class WrapperTest extends TestCase
{
    /**
     * @testdox it can correctly resolve a wrapped template
     */
    public function testResolveWrapper()
    {
        $template = '{{$ from lists.todos
            <div>
                <h1>{{ title }}</h1>
                <p>{{ body }}</p>
            </div>
        $}}';

        $result = RocketWrapper::resolveWrapper($template);
        $this->assertEquals("<div><h1>{{ lists.todos.title }}</h1><p>{{ lists.todos.body }}</p></div>", TestTools::cleanHtml($result));
    }

    /**
     * @testdox it is correctly resolved when nested with a loop
     */
    public function testResolveWrapperWithLoop()
    {
        $template = '{{$ from lists.todos
            <div>
                <h1>{{ title }}</h1>
                <p>{{ body }}</p>
            </div>
            {{% for item in items 
                <span>{{ item }}</span>
            %}}
            $}}';

        $result = RocketWrapper::resolveWrapper($template);
        $this->assertEquals("<div><h1>{{ lists.todos.title }}</h1><p>{{ lists.todos.body }}</p></div>{{% for item in lists.todos.items<span>{{ item }}</span>%}}", TestTools::cleanHtml($result));
    }

    /**
     * @testdox it is correctly resolved when nested with a double loop
     */
    public function testResolveWrapperWithDoubleLoop()
    {
        $template = '{{$ from lists.todos
            <div>
                <h1>{{ title }}</h1>
                <p>{{ body }}</p>
            </div>
            {{% for item in items 
                <span>{{ item }}</span>
            %}}
            {{% for property in properties 
                <span>{{ property }}</span>
            %}}
          
            $}}';

        $result = RocketWrapper::resolveWrapper($template);
        $this->assertEquals("<div><h1>{{ lists.todos.title }}</h1><p>{{ lists.todos.body }}</p></div>{{% for item in lists.todos.items<span>{{ item }}</span>%}}{{% for property in lists.todos.properties<span>{{ property }}</span>%}}", TestTools::cleanHtml($result));
    }

    /**
     * @testdox it is correctly resolved when nested with a conditional
     */
    public function testResolveWrapperWithConditional()
    {
        $template = '{{$ from lists.todos
            {{~ if a
                {{ a }}
            ~}}
            $}}';

        $result = RocketWrapper::resolveWrapper($template);
        $this->assertEquals("{{~ if lists.todos.a{{ lists.todos.a }}~}}", TestTools::cleanHtml($result));
    }
}