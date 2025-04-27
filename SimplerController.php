<?php
// Set headers for JSON response
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include repository
require_once 'BooksRepository.php';

// Create instance of repository
$repository = new BooksRepository();

// Get the HTTP method and path
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));

// The first part of the path determines the resource (books)
$resource = array_shift($request) ?? '';

// The second part of the path is the ID for individual resource requests
$id = array_shift($request) ?? null;

// Process the request based on method and path
switch ($method) {
    case 'GET':
        if ($resource === 'books') {
            if ($id) {
                // Get a specific book
                $book = $repository->getById($id);
                if ($book) {
                    http_response_code(200);
                    echo json_encode($book);
                } else {
                    http_response_code(404);
                    echo json_encode(["message" => "Book not found"]);
                }
            } else {
                // Get all books
                $books = $repository->getAll();
                http_response_code(200);
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