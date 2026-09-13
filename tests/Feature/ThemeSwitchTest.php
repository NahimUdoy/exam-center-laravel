<?php

namespace Tests\Feature;

use App\Support\Theme;
use Tests\TestCase;

class ThemeSwitchTest extends TestCase
{
    public function test_theme_query_sets_cookie_and_document_theme(): void
    {
        $this->get('/?theme=classic')
            ->assertOk()
            ->assertCookie(Theme::COOKIE, 'classic')
            ->assertSee('data-theme="classic"', false);

        $this->get('/?theme=studio')
            ->assertOk()
            ->assertCookie(Theme::COOKIE, 'studio')
            ->assertSee('data-theme="studio"', false);
    }

    public function test_invalid_theme_query_is_ignored(): void
    {
        $this->get('/?theme=neon')
            ->assertOk()
            ->assertSee('data-theme="studio"', false);
    }
}
