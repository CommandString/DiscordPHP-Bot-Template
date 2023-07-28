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

        $this->assertEquals('DANGER', $button->getLabel());
        $this->assertEquals(Button::STYLE_DANGER, $button->getStyle());

        $button = newButton(Button::STYLE_PRIMARY, 'PRIMARY', 'primary_button');
        $this->assertEquals('PRIMARY', $button->getLabel());
        $this->assertEquals(Button::STYLE_PRIMARY, $button->getStyle());
        $this->assertEquals('primary_button', $button->getCustomId());
    }
}
