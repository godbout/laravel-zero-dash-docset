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
    public function the_header_gets_hidden_in_the_dash_docset_files()
    {
        $crawler = HtmlPageCrawler::create(
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertFalse(
            $crawler->filter('header')->hasClass('hidden')
        );


        $crawler = HtmlPageCrawler::create(
            Storage::get($this->docset->innerIndex())
        );

        $this->assertTrue(
            $crawler->filter('header')->hasClass('hidden')
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
            $crawler->filter('.DocSearch-content')->hasClass('lg:ml-10')
        );


        $crawler = HtmlPageCrawler::create(
            $this->docset->innerIndex()
        );

        $this->assertFalse(
            $crawler->filter('.DocSearch-content')->hasClass('lg:ml-10')
        );
    }

    /** @test */
    public function the_top_margin_gets_updated_in_the_dash_docset_files()
    {
        $crawler = HtmlPageCrawler::create(
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertNull(
            $crawler->filter('h1')->css('margin-top')
        );


        $crawler = HtmlPageCrawler::create(
            Storage::get($this->docset->innerIndex())
        );

        $this->assertEquals(
            '1rem',
            $crawler->filter('h1')->css('margin-top')
        );
    }

    /** @test */
    public function the_bottom_margin_gets_updated_in_the_dash_docset_files()
    {
        $crawler = HtmlPageCrawler::create(
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertTrue(
            $crawler->filter('section > div > div')->hasClass('mb-20')
        );


        $crawler = HtmlPageCrawler::create(
            $this->docset->innerIndex()
        );

        $this->assertFalse(
            $crawler->filter('section > div > div')->hasClass('mb-20')
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
