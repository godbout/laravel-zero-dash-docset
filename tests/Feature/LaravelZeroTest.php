<?php

namespace Tests\Feature;

use App\Docsets\LaravelZero;
use Godbout\DashDocsetBuilder\Services\DocsetBuilder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Wa72\HtmlPageDom\HtmlPageCrawler;

class LaravelZeroTest extends TestCase
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
    public function it_has_a_table_of_contents()
    {
        Config::set(
            'database.connections.sqlite.database',
            "storage/{$this->docset->databaseFile()}"
        );

        $this->assertNotEquals(0, DB::table('searchIndex')->count());
    }

    /** @test */
    public function the_header_gets_removed_from_the_dash_docset_files()
    {
        $header = '<header';

        $this->assertStringContainsString(
            $header,
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            $header,
            Storage::get($this->docset->innerIndex())
        );
    }

    /** @test */
    public function the_left_sidebar_gets_removed_from_the_dash_docset_files()
    {
        $leftSidebarFirstPart = 'class="docs-nav';

        $this->assertStringContainsString(
            $leftSidebarFirstPart,
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            $leftSidebarFirstPart,
            Storage::get($this->docset->innerIndex())
        );


        $leftSidebarSecondPart = 'class="hidden lg:block mt-1"';

        $this->assertStringContainsString(
            $leftSidebarSecondPart,
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            $leftSidebarSecondPart,
            Storage::get($this->docset->innerIndex())
        );
    }

    /** @test */
    public function the_edit_this_page_link_gets_removed_from_the_dash_docset_files()
    {
        $link = 'class="absolute h-8 hidden';

        $this->assertStringContainsString(
            $link,
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            $link,
            Storage::get($this->docset->innerIndex())
        );
    }

    /** @test */
    public function the_footer_gets_removed_from_the_dash_docset_files()
    {
        $footer = '<footer';

        $this->assertStringContainsString(
            $footer,
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            $footer,
            Storage::get($this->docset->innerIndex())
        );
    }

    /** @test */
    public function the_container_width_gets_updated_in_the_dash_docset_files()
    {
        $crawler = HtmlPageCrawler::create(
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertTrue(
            $crawler->filter('section.container > div > div')->hasClass('lg:w-3/5')
        );


        $crawler = HtmlPageCrawler::create(
            $this->docset->innerIndex()
        );

        $this->assertFalse(
            $crawler->filter('section.container > div > div')->hasClass('lg:w-3/5')
        );
    }

    /** @test */
    public function the_bottom_padding_gets_updated_in_the_dash_docset_files()
    {
        $crawler = HtmlPageCrawler::create(
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertTrue(
            $crawler->filter('section > div > div')->hasClass('pb-16')
        );


        $crawler = HtmlPageCrawler::create(
            $this->docset->innerIndex()
        );

        $this->assertFalse(
            $crawler->filter('section > div > div')->hasClass('pb-16')
        );
    }

    /** @test */
    public function the_JavaScript_tags_get_removed_from_the_dash_docset_files()
    {
        $this->assertStringContainsString(
            '<script ',
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            '<script ',
            $this->docset->innerIndex()
        );
    }

    /** @test */
    public function there_is_a_set_of_manual_icons_prepared_for_this_docset()
    {
        $this->assertFileExists(
            "storage/{$this->docset->code()}/icons/icon.png"
        );

        $this->assertFileExists(
            "storage/{$this->docset->code()}/icons/icon@2x.png"
        );
    }
}
