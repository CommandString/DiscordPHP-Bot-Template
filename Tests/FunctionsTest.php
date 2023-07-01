<?php

use Discord\Builders\Components\Button;
use Monolog\Test\TestCase;

use function Core\messageWithContent;
use function Core\newButton;

class FunctionsTest extends TestCase
{
    public function testItCreatesAMessageWithContent(): void
    {
        $message = messageWithContent('Hello World');

        $this->assertEquals([
            'content' => 'Hello World',
        ], $message->jsonSerialize());
    }

    public function testItCreatesAButton(): void
    {
        $button = newButton(Button::STYLE_DANGER, 'DANGER');

        $this->assertEquals($button->getLabel(), 'DANGER');
        $this->assertEquals($button->getStyle(), Button::STYLE_DANGER);

        $button = newButton(Button::STYLE_PRIMARY, 'PRIMARY', 'primary_button');
        $this->assertEquals($button->getLabel(), 'PRIMARY');
        $this->assertEquals($button->getStyle(), Button::STYLE_PRIMARY);
        $this->assertEquals($button->getCustomId(), 'primary_button');
    }
}
