<?php
require_once 'Book.php';

class BooksRepository {
    private $books = [];
    
    public function __construct() {
        // Initialize with some hardcoded books
        $this->books = [
            new Book(1, "The Great Gatsby", "F. Scott Fitzgerald", 9.99),
            new Book(2, "To Kill a Mockingbird", "Harper Lee", 12.50),
            new Book(3, "1984", "George Orwell", 10.75),
            new Book(4, "The Hobbit", "J.R.R. Tolkien", 14.99)
        ];
    }
    
    public function getAll() {
        return $this->books;
    }
    
    public function getById($id) {
        foreach ($this->books as $book) {
            if ($book->id == $id) {
                return $book;
            }
        }
        return null;
    }
    
    public function add($book) {
        // Generate a new ID if not provided
        if (!$book->id) {
            $maxId = 0;
            foreach ($this->books as $existingBook) {
                if ($existingBook->id > $maxId) {
                    $maxId = $existingBook->id;
                }
            }
            $book->id = $maxId + 1;
        }
        
        $this->books[] = $book;
        return $book;
    }
    
    public function update($id, $book) {
        for ($i = 0; $i < count($this->books); $i++) {
            if ($this->books[$i]->id == $id) {
                $book->id = $id; // Ensure ID stays the same
                $this->books[$i] = $book;
                return true;
            }
        }
        return false;
    }
    
    public function delete($id) {
        for ($i = 0; $i < count($this->books); $i++) {
            if ($this->books[$i]->id == $id) {
                array_splice($this->books, $i, 1);
                return true;
            }
        }
        return false;
    }
}