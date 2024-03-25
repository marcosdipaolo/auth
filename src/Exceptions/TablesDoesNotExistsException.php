<?php

namespace MDP\Auth\Exceptions;

class TablesDoesNotExistsException extends \Exception
{
  public function __construct(
      protected string $table,
  )
  {
      parent::__construct("Table '{$table}' doesn't exist in the database.");
  }
}