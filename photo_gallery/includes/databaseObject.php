<?php 
require_once(LIB_PATH.DS."database.php");    

class DatabaseObject {

protected static $table_name;
protected static $field_name;
protected static $db_fields;


public static function FindBySql($sql="") {
       global $database;
       $result_set = $database->Query($sql);
       $object_array = array();
       while ($row = $database->FetchArray($result_set)) {
         $object_array[] = static::Instantiate($row);
       }
        return $object_array;
}

public static function FindAll(){
     return static::FindBySql("SELECT * FROM ".static::$table_name ." ORDER BY ".static::$field_name. " ASC");
}

public static function FindById($id=0) {
      global $database;
      $result_array = static::FindBySql("SELECT * FROM ".static::$table_name ." WHERE id = {$id} LIMIT 1");
      return !empty($result_array) ? array_shift($result_array) : false;
}

private static function Instantiate($record) {
      $object = new static;

       foreach ($record as $attribute => $value) {
         if ($object->HasAttribute($attribute)) {
           $object->$attribute = $value;
         } 
       }
      return $object;
}

private function HasAttribute($attribute){
      $object_vars = $this->Attributes();
      return array_key_exists($attribute, $object_vars);
}

protected function Attributes(){
      $attributes = array();
      foreach (static::$db_fields as $field) {
        if (property_exists($this, $field)) {
          $attributes[$field]=$this->$field;
        }
      }
      return $attributes;
}

protected function SanitizedAttributes() {
  global $database;
  $clean_attributes = array();
  foreach ($this->Attributes() as $key => $value) {
    $clean_attributes[$key] = $database->EscapeValue($value);
  }
  return $clean_attributes;
} 

public function Create(){
 global $database;
$attributes = $this->Attributes();

     $query = "INSERT INTO ". static::$table_name." (";
     $query .= join(", " , array_keys($attributes));
     $query .= ")  VALUES (' ";
     $query .= join("', '" , array_values($attributes));
     $query .= " ')";
            return $result = $database->Query($query);
}

public function Update(){
     global $database;

     $attributes = $this->SanitizedAttributes();
     $attributes_pairs = array();
     foreach ($attributes as $key => $value) {
       $attributes_pairs[] = "{$key} = '{$value}' ";
     }

     $query = "UPDATE ". self::$table_name." SET ";
     $query .= join(", ", $attributes_pairs);
     $query .= " WHERE id=" . $database->EscapeValue($this->id);
 
     $database->Query($query);
      return($database->AffectedRows()==1) ?  true :  false;
}

public function Delete(){
      global $database;
      $query = "DELETE FROM " . static:: $table_name;
      $query .= " WHERE id=" . $database->EscapeValue($this->id);
      $query .= " LIMIT 1";
      $database->Query($query);
      return($database->AffectedRows()); 
}

}



?>
