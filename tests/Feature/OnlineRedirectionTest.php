<?php

namespace Tests\Feature;

use App\Docsets\LaravelZero;
use Godbout\DashDocsetBuilder\Services\DocsetBuilder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use Wa72\HtmlPageDom\HtmlPageCrawler;

class OnlineRedirectionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->docset = new LaravelZero();
        $this->builder = new DocsetBuilder($this->docset);

        if (! Storage::exists($this->docset->downloadedDirectory())) {
            fwrite(STDOUT, PHP_EOL . PHP_EOL . "\e[1;33mGrabbing laravel-zero..." . PHP_EOL);
            Artisan::call('grab laravel-zero');
        }

        if (! Storage::exists($this->docset->file())) {
            fwrite(STDOUT, PHP_EOL . PHP_EOL . "\e[1;33mPackaging laravel-zero..." . PHP_EOL);
            Artisan::call('package laravel-zero');
        }
    }

    /** @test */
    public function the_online_redirection_html_comment_exists_in_the_docset_files()
    {
        $crawler = HtmlPageCrawler::create(
            Storage::get($this->docset->downloadedDirectory() . '/' . $this->docset->index())
        );

        $this->assertFalse(
            Str::contains($crawler->getInnerHtml(), 'Online page')
        );

        $crawler = HtmlPageCrawler::create(
            Storage::get($this->docset->innerDirectory() . '/' . $this->docset->index())
        );

        $this->assertTrue(
            Str::contains($crawler->getInnerHtml(), 'Online page')
        );
    }
}
