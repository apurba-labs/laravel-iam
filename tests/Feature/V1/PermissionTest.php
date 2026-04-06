<?php
namespace ApurbaLabs\IAM\Tests\Feature\V1;

use ApurbaLabs\IAM\Tests\TestCase;

use ApurbaLabs\IAM\Exceptions\InvalidPermissionException;
use ApurbaLabs\IAM\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiPermissionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_throws_exception_if_slug_is_missing()
    {
        $this->expectException(InvalidPermissionException::class);
        $this->expectExceptionMessage("A unique 'slug' is required");
        // This should trigger the exception
        Permission::create(['name' => 'Just a Name']);
    }

    /** @test */
    public function it_throws_exception_with_correct_message_if_slug_is_missing()
    {
        // Tell PHPUnit to expect the class
        $this->expectException(InvalidPermissionException::class);
        
        // Tell PHPUnit to expect this EXACT string
        $this->expectExceptionMessage("A unique 'slug' is required to create a Permission");

        // Trigger the action
        Permission::create(['name' => 'Just a Name']); 
    }

    /** @test */
    public function it_works_fine_when_slug_is_provided()
    {
        // This should NOT throw an exception
        $permission = Permission::create([
            'slug' => 'invoice.edit',
            'name' => 'Edit Invoice'
        ]);

        $this->assertEquals('invoice.edit', $permission->slug);
        $this->assertEquals('invoice', $permission->resource);
    }

    /** @test */
    public function debug_exception_message()
    {
        try {
            Permission::create(['name' => 'Just a Name']);
        } catch (\Exception $e) {
            // This will print the message in your terminal during the test
            dump($e->getMessage()); 
            $this->assertInstanceOf(InvalidPermissionException::class, $e);
            return;
        }

        $this->fail('Exception was not thrown!');
    }
}