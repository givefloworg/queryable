<?php

namespace Queryable\Tests;

use Queryable\DB;
use Queryable\Tests\Concerns\QueryBuilderTestCase;

/**
 * init() replaces the whole schema, which makes it unusable for anyone who is
 * not booting the application: a second caller silently drops the first
 * caller's tables, and withMeta() then throws for them.
 */
class RegisterSchemaTest extends QueryBuilderTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::reset();
    }

    private function meta(string $key): array
    {
        return ['meta' => [
            'table' => 'postmeta',
            'foreignKey' => 'post_id',
            'primaryKey' => 'ID',
            'aliases' => ['value' => $key],
        ]];
    }

    public function testRegisterSchemaMakesWithMetaUsable(): void
    {
        DB::registerSchema('posts', $this->meta('_price'));

        $this->assertStringContainsString(
            "LEFT JOIN postmeta AS meta_value ON posts.ID = meta_value.post_id",
            DB::table('posts')->select('ID')->withMeta('value')->toSQL(),
        );
    }

    public function testRegisterSchemaKeepsWhatInitPutThere(): void
    {
        DB::init(['schema' => ['posts' => $this->meta('_price')]]);
        DB::registerSchema('comments', $this->meta('_rating'));

        $this->assertArrayHasKey('posts', DB::getSchema());
        $this->assertArrayHasKey('comments', DB::getSchema());
    }

    public function testTwoRegistrarsDoNotClobberEachOther(): void
    {
        DB::registerSchema('posts', $this->meta('_price'));
        DB::registerSchema('comments', $this->meta('_rating'));

        $this->assertSame('_price', DB::getSchema('posts')['meta']['aliases']['value']);
        $this->assertSame('_rating', DB::getSchema('comments')['meta']['aliases']['value']);
    }

    public function testGetSchemaIsEmptyForAnUnknownTable(): void
    {
        $this->assertSame([], DB::getSchema('nope'));
    }
}
