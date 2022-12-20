<?php

namespace Sideso\Hablame\Tests;

use Sideso\SMS\Message;

class MessageTest extends TestCase
{
    /** @test */
    public function it_can_accept_a_content_when_constructing_a_message(): void
    {
        $message = new Message('hello');

        $this->assertEquals('hello', $message->content);
    }

    /** @test */
    public function it_can_set_the_content(): void
    {
        $message = (new Message())->content('hello');

        $this->assertEquals('hello', $message->content);
    }
}
