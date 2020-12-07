<?php

namespace Tests\Unit;

use App\Docsets\LaravelZero;
use Godbout\DashDocsetBuilder\Services\DocsetBuilder;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LaravelZeroTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->docset = new LaravelZero();
        $this->builder = new DocsetBuilder($this->docset);
    }

    /** @test */
    public function it_can_generate_a_table_of_contents()
    {
        $toc = $this->docset->entries(
            $this->docset->downloadedDirectory() . '/' . $this->docset->url() . '/docs/logging.html'
        );

        $this->assertNotEmpty($toc);
    }

    /** @test */
    public function it_can_format_the_documentation_files()
    {
        $footer = '<footer';

        $this->assertStringContainsString(
            $footer,
            Storage::get($this->docset->downloadedDirectory() . '/' . $this->docset->url() . '/docs/logging.html')
        );

        $this->assertStringNotContainsString(
            $footer,
            $this->docset->format(
                $this->docset->downloadedDirectory() . '/' . $this->docset->url() . '/docs/logging.html'
            )
        );
    }
}
