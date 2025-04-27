<?php
// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set headers for JSON response
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include repository
require_once 'BooksRepository.php';

// Create instance of repository
$repository = new BooksRepository();

// Get the HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Parse the URL
$request_uri = $_SERVER['REQUEST_URI'];
$uri_parts = explode('/', trim($request_uri, '/'));

// Find 'books-api' in the URL
$api_pos = array_search('books-api', $uri_parts);
$resource = isset($uri_parts[$api_pos + 1]) ? $uri_parts[$api_pos + 1] : '';
$id = isset($uri_parts[$api_pos + 2]) ? $uri_parts[$api_pos + 2] : null;

// If accessing the base URL, return a welcome message
if (empty($resource)) {
    echo json_encode([
        "message" => "Welcome to Books API. Use /books endpoint to access the API.",
        "available_endpoints" => [
            "GET /books" => "Get all books",
            "GET /books/{id}" => "Get book by ID",
            "POST /books" => "Create a new book",
            "PUT /books/{id}" => "Update a book",
            "DELETE /books/{id}" => "Delete a book"
        ]
    ]);
    exit;
}

// Process the request based on method and path
switch ($method) {
    case 'GET':
        if ($resource === 'books') {
            if ($id) {
                // Get a specific book
                $book = $repository->getById($id);
                if ($book) {
                    echo json_encode($book);
                } else {
                    http_response_code(404);
                    echo json_encode(["message" => "Book not found"]);
                }
            } else {
                // Get all books
                $books = $repository->getAll();
                echo json_encode($books);
            }
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Resource not found"]);
        }
        break;
        
    case 'POST':
        if ($resource === 'books') {
            // Add a new book
            $data = json_decode(file_get_contents("php://input"));
            if (!$data || !isset($data->title) || !isset($data->author) || !isset($data->price)) {
                http_response_code(400);
                echo json_encode(["message" => "Invalid data"]);
                break;
            }
            
            $newBook = new Book(null, $data->title, $data->author, $data->price);
            $addedBook = $repository->add($newBook);
            
            http_response_code(201); // Created
            echo json_encode($addedBook);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Resource not found"]);
        }
        break;
        
    case 'PUT':
        if ($resource === 'books' && $id) {
            // Update an existing book
            $data = json_decode(file_get_contents("php://input"));
            if (!$data || !isset($data->title) || !isset($data->author) || !isset($data->price)) {
                http_response_code(400);
                echo json_encode(["message" => "Invalid data"]);
                break;
            }
            
            $book = new Book($id, $data->title, $data->author, $data->price);
            $success = $repository->update($id, $book);
            
            if ($success) {
                http_response_code(200);
                echo json_encode($book);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Book not found"]);
            }
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Resource not found"]);
        }
        break;
        
    case 'DELETE':
        if ($resource === 'books' && $id) {
            // Delete a book
            $success = $repository->delete($id);
            
            if ($success) {
                http_response_code(204); // No Content
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Book not found"]);
            }
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Resource not found"]);
        }
        break;
        
    default:
        // Unsupported method
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}