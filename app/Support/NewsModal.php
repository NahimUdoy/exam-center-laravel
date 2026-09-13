<?php

namespace App\Support;

use App\Support\Cms\Site;

class NewsModal
{
    public static function payload(): ?array
    {
        $news = Site::get()['newsModal'] ?? [];

        if (! ($news['enabled'] ?? false) || blank($news['title'] ?? null)) {
            return null;
        }

        return $news;
    }

    public static function flash(): void
    {
        if (! self::payload()) {
            return;
        }

        session()->flash('show_news_modal', true);
    }
}
