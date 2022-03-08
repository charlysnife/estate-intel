<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Author;

class RoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_external_books_route_exist()
    {
        $this->withoutDeprecationHandling();

        $data = [
            'status_code'=>200 || 404
        ];

        $this->get('/api/external-books')->assertJson($data);
    }

    /**
     * 
     * A test code test external book route when result is found
     * 
     */
    public function test_external_books_route_when_result_found() {
        $this->withoutDeprecationHandling();

        $data = [
            'status_code'=>200,
            'status'=>'success'
        ];

        $this->get('/api/external-books?name=A Game of Thrones')->assertJson($data);
    }

    /**
     * 
     * A test code to test external book route when result is not found
     * 
     */

    public function test_external_books_route_when_result_not_found() {
        $this->withoutDeprecationHandling();

        $data = [
            'status_code'=>404,
            'status'=>'not found',
            'data'=>[]
        ];

        $this->get('/api/external-books?name=A Game of Throne')->assertJson($data);
    }

    public function test_external_books_route_validation()
    {
        $this->withoutDeprecationHandling();

        $data = [
            'status_code'=>404,
            'message'=>'Name of book required in your request'
        ];

        $this->get('/api/external-books')->assertJson($data);
    }

    /**
     * 
     * Test create book route with parameter
     * 
     */

    public function test_create_book_route_with_parameter() 
    {
        $this->withoutDeprecationHandling();

        $book = Book::factory()->definition();
        $author = Author::factory()->definition();

        $parameter = [
            'name'=>$book['name'],
            'isbn'=>$book['isbn'],
            'authors'=>[
                ['name'=>$author['author_name']]
            ],
            'country'=>$book['country'],
            'number_of_pages'=>$book['number_of_pages'],
            'publisher'=>$book['publisher'],
            'release_date'=>$book['release_date']
        ];

        $responseData = [
            'status_code'=>201,
            'status'=>'success',
            'data'=> [
                'book'=> [
                    'name'=>$book['name'],
                    'isbn'=>$book['isbn'],
                    'authors'=>[
                        $author['author_name']
                    ],
                    'country'=>$book['country'],
                    'number_of_pages'=>$book['number_of_pages'],
                    'publisher'=>$book['publisher'],
                    'release_date'=>$book['release_date']
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/books', $parameter);
        // $response->dump();
        $response->assertStatus(201)->assertJson($responseData);
    }

    /**
     * 
     * Fetch books
     * 
     */

     public function test_read_books() {
        $this->withoutDeprecationHandling();
        
        $response = $this->get('/api/v1/books');

        $response->assertStatus(200);
     }
}
