<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/functions/TestTools.php';
require_once __DIR__ . '/../../rocket/rocket.php';

final class RocketTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set the global variable to a known state for testing
        $GLOBALS['template'] = '
        <main>
            {{~ if articles
                <section>
                {{% for article of articles
                    <article>
                    <h1>{{article.header}}</h1>
                    <p>{{article.text}}</p>
                    {{~ if article.footer
                        <footer>{{article.footer}}</footer>
                    ~}}
                    </article>
                %}}
                </section>
            ~}}
        </main>
        ';

        $GLOBALS['viewmodel'] = [
            "articles" => [
                [
                    "header" => 'Article 1',
                    "text" => '1) Lorem ipsum dolor'
                ],
                [
                    "header" => 'Article 2',
                    "text" => '2) Amet and some other stuff'
                ],
                [
                    "header" => 'Article 3',
                    "text" => '3) Hello world.',
                    "footer" => "Written by Edgar Allan Poe"
                ]
            ]
        ];
    }

    /**
     * @testdox it can resolve a template with nested statements
     */
    public function testResolvingConstructedTemplates()
    {
        $result = Rocket::buildTemplate($GLOBALS['template'], $GLOBALS['viewmodel']);

        $this->assertEquals("<main><section><article><h1>{{articles.0.header}}</h1><p>{{articles.0.text}}</p></article><article><h1>{{articles.1.header}}</h1><p>{{articles.1.text}}</p></article><article><h1>{{articles.2.header}}</h1><p>{{articles.2.text}}</p><footer>{{articles.2.footer}}</footer></article></section></main>", TestTools::cleanHtml($result));
    }
}