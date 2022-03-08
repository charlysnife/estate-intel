<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Book;
use App\Models\Author;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    //
    public function externalBooks(Request $request) {
        if($request->name) {
            $response = Http::get(env('API_ENDPOINT') . 'books?name=' . urlencode($request->name));
            $array = $response->json();

            if(sizeof($array)) {
                $response = [
                    'status_code'=>200,
                    'status'=>'success'
                ];
                $response_data = [];

                foreach($array as $res) {
                    $released_date = explode('T', $res['released']);
                    $response_data[] = [
                        'name' => $res['name'],
                        'isbn' => $res['isbn'],
                        'authors' => $res['authors'],
                        'number_of_pages' => $res['numberOfPages'],
                        'publisher' => $res['publisher'],
                        'country' => $res['country'],
                        'release_date' => $released_date[0]
                    ];
                }

                $response['data'] = $response_data;

                return response()->json($response, 200);
            } else {
                $response = [
                    'status_code'=>404,
                    'status'=>'not found',
                    'data'=>[]
                ];
    
                return response()->json($response, 404);
            }
        } else {
            $response = [
                'status_code'=>404,
                'message'=>'Name of book required in your request'
            ];

            return response()->json($response, 404);
        }
    }

    public function createBook(Request $request) {
        $rules = [
            'name' => 'required|unique:books',
            'country' => 'required',
            'number_of_pages' => 'required|integer',
            'isbn' => 'required',
            'publisher' => 'required',
            'release_date' => 'required',
            'authors' => 'required'
        ];

        $messages = [
            'name.unique' => $request->name . ' already exist',
            'name.required' => 'Name of book is required',
            'country.required' => 'Name of country is required',
            'number_of_pages.required' => 'Number of pages is required',
            'isbn.required' => 'Isbn of book is required',
            'publisher.required' => 'Name of publisher is required',
            'publisher.integer' => 'Number of book pages should be a number',
            'release_date.required' => 'Release date of book is required',
            'authors.required' => 'Name of author(s) is required' 
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()) {
            $response = [
                'status_code'=>200,
                'status'=>'success',
                'errors'=> $validator->errors()
            ];

            return response()->json($response, 200);
        } else {
            $book = new Book;
            $book->name = $request->name;
            $book->isbn = $request->isbn;
            $book->country = $request->country;
            $book->publisher = $request->publisher;
            $book->release_date = $request->release_date;
            $book->number_of_pages = $request->number_of_pages;

            $book->save();

            $authors = [];
            
            foreach($request->authors as $author) {
                $book_authors = new Author;
                $authors[] = $author['name'];
                $book_authors->book_id = $book->id;
                $book_authors->author_name = $author['name'];
                $book_authors->save();
            }

            $response = [
                'status_code'=>201,
                'status'=>'success',
                'data'=> [
                    'book'=> [
                        'name' => $request->name,
                        'isbn' => $request->isbn,
                        'authors' => $authors,
                        'number_of_pages' => $request->number_of_pages,
                        'publisher' => $request->publisher,
                        'country' => $request->country,
                        'release_date' => $request->release_date
                    ]
                ],
                'book'=>Book::first(),
                'id'=>$book->id,
                'db'=>ENV('DB_DATABASE')
            ];
            
            return response()->json($response, 201);
        }
    }

    public function readBook(Request $request) {
        if(sizeof(Book::all())) {
            if(isset($request->name) || isset($request->country) || isset($request->publisher) || isset($request->release_date)) {
                $books = null;
                
                if(isset($request->name)) {
                    $books = Book::with('authors')->where('name', $request->name)->get();
                }

                if(isset($request->country)) {
                    $books = Book::with('authors')->where('country', $request->country)->get();
                }

                if(isset($request->publisher)) {
                    $books = Book::with('authors')->where('publisher', $request->publisher)->get();
                }

                if(isset($request->release_date)) {
                    $books = Book::with('authors')->where('release_date', $request->release_date)->get();
                }

                if($books === null || sizeof($books) === 0) {
                    $response = [
                        'status_code'=>200,
                        'status'=>'success',
                        'data'=>[]
                    ];
    
                    return response()->json($response, 200);
                } else {
                    $collection = collect($books)->map(function ($book) {
                        $authors = collect($book->authors)->map(function ($author) {
                            return $author->author_name;
                        });
    
                        $data = [
                            'id'=>$book->id,
                            'name'=>$book->name,
                            'isbn'=>$book->isbn,
                            'authors'=>$authors,
                            'country'=>$book->country,
                            'number_of_pages'=>$book->number_of_pages,
                            'publisher'=>$book->publisher,
                            'release_date'=>$book->release_date
                        ];
                        return $data;
                    });
    
                    $response = [
                        'status_code'=>200,
                        'status'=>'success',
                        'data'=>$collection
                    ];
    
                    return response()->json($response, 200);
                }
            } else {
                $books = Book::with('authors')->get();
                $collection = collect($books)->map(function ($book) {
                    $authors = collect($book->authors)->map(function ($author) {
                        return $author->author_name;
                    });

                    $data = [
                        'id'=>$book->id,
                        'name'=>$book->name,
                        'isbn'=>$book->isbn,
                        'authors'=>$authors,
                        'country'=>$book->country,
                        'number_of_pages'=>$book->number_of_pages,
                        'publisher'=>$book->publisher,
                        'release_date'=>$book->release_date
                    ];
                    return $data;
                });

                $response = [
                    'status_code'=>200,
                    'status'=>'success',
                    'data'=>$collection
                ];

                return response()->json($response, 200);
            }
        } else {
            $response = [
                'status_code'=>200,
                'status'=>'success',
                'data'=>[]
            ];

            return response()->json($response, 200); 
        }
    }

    public function updateBook(Request $request, $id) {
        $rules = [
            'name' => 'required',
            'country' => 'required',
            'number_of_pages' => 'required|integer',
            'isbn' => 'required',
            'publisher' => 'required',
            'release_date' => 'required',
            'authors' => 'required'
        ];

        $messages = [
            'name.required' => 'Name of book is required',
            'country.required' => 'Name of country is required',
            'number_of_pages.required' => 'Number of pages is required',
            'isbn.required' => 'Isbn of book is required',
            'publisher.required' => 'Name of publisher is required',
            'publisher.integer' => 'Number of book pages should be a number',
            'release_date.required' => 'Release date of book is required',
            'authors.required' => 'Name of author(s) is required' 
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()) {
            $response = [
                'status_code'=>200,
                'status'=>'success',
                'errors'=> $validator->errors()
            ];

            return response()->json($response, 200);
        } else {
            Book::where('id', $id)->update(['name' => $request->name, 'isbn' => $request->isbn, 'country' => $request->country, 'publisher' => $request->publisher, 'release_date' => $request->release_date, 'number_of_pages' => $request->number_of_pages]);
            
            $book_authors = Author::where('book_id', $id)->get();
            $authors = [];
            
            foreach($request->authors as $index => $author) {
                $authors[] = $author['name'];

                if(isset($book_authors[$index]['id'])) {
                    Author::updateOrCreate(['book_id'=>$id, 'author_name'=>$book_authors[$index]['author_name']], ['author_name'=>$author['name']]);
                } else {
                    $new_book_authors = new Author;
                    $new_book_authors->book_id = $id;
                    $new_book_authors->author_name = $author['name'];
                    $new_book_authors->save();
                }
                
            }

            $response = [
                'status_code'=>200,
                'status'=>'success',
                'data'=> [
                    'name' => $request->name,
                    'isbn' => $request->isbn,
                    'authors' => $authors,
                    'number_of_pages' => $request->number_of_pages,
                    'publisher' => $request->publisher,
                    'country' => $request->country,
                    'release_date' => $request->release_date
                ]
            ];

            return response()->json($response, 200);
        }
    }

    public function deleteBook($id) {
        $book = Book::where('id', $id)->get();
        if(sizeof($book)) {
            Book::where('id', $id)->with('authors')->delete();
            Author::where('book_id', $id)->delete();
            $response = [
                'status_code' => 204,
                'status' => 'success',
                'message' => "The book '" . $book[0]->name . "' was deleted successfully",
                'data' => []
            ];

            return response()->json($response, 200);
        } else {
            $response = [
                'status_code'=>200,
                'status'=>'success',
                'data'=>[]
            ];

            return response()->json($response, 200);
        }
    }

    public function showBook($id) {
        $books = Book::where('id', $id)->with('authors')->get();
        if(sizeof($books)) {
            $collection = collect($books)->map(function ($book) {
                $authors = collect($book->authors)->map(function ($author) {
                    return $author->author_name;
                });
    
                $data = [
                    'id'=>$book->id,
                    'name'=>$book->name,
                    'isbn'=>$book->isbn,
                    'authors'=>$authors,
                    'country'=>$book->country,
                    'number_of_pages'=>$book->number_of_pages,
                    'publisher'=>$book->publisher,
                    'release_date'=>$book->release_date
                ];
                return $data;
            });

            $response = [
                'status_code'=>200,
                'status'=>'success',
                'data'=>$collection
            ];
    
            return response()->json($response, 200);
        } else {
            $response = [
                'status_code'=>200,
                'status'=>'success',
                'data'=>[]
            ];

            return response()->json($response, 200);
        }
    }
}
