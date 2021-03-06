<?php

class Page_Model extends Model
{
	private $table_name;

	public function __construct($db)
	{
		parent::__construct($db);
		
		$this->table_name = 'pages';
	}

	public function GetPage($id)
	{
		$this->row_item = array();

		$main_page = 0;
		$system_page = 0;
		$show_all_types = 1;
		$visible = 1;
		
		$this->UpdatePreviews($id);

		try
		{
			$query = 	'SELECT title, contents, description, category_id, user_login, ' . $this->table_name . '.modified, previews, ' . $this->table_name . '.author_id AS user_id' .
						' FROM ' . $this->table_name .
						' INNER JOIN users ON users.id = ' . $this->table_name . '.author_id' .
						' WHERE (:show_all_types OR main_page = :main_page AND system_page = :system_page)' .
						' AND ' . $this->table_name . '.id = :id AND visible = :visible';
			
			$statement = $this->db->prepare($query);

			$statement->bindValue(':id', $id, PDO::PARAM_INT); 
			$statement->bindValue(':main_page', $main_page, PDO::PARAM_INT); 
			$statement->bindValue(':system_page', $system_page, PDO::PARAM_INT); 
			$statement->bindValue(':show_all_types', $show_all_types, PDO::PARAM_INT); 
			$statement->bindValue(':visible', $visible, PDO::PARAM_INT); 

			$statement->execute();
			
			$this->row_item = $statement->fetch(PDO::FETCH_ASSOC);			
		}
		catch (PDOException $e)
		{
			die ($e->getMessage());
		}

		return $this->row_item;
	}

	private function UpdatePreviews($id)
	{
		$affected_rows = 0;

		try
		{
			$query =	'UPDATE ' . $this->table_name .
						' SET previews = previews + 1' .
						' WHERE id = :id';

			$statement = $this->db->prepare($query);

			$statement->bindValue(':id', $id, PDO::PARAM_INT); 
			
			$statement->execute();
			
			$affected_rows = $statement->rowCount();
		}
		catch (PDOException $e)
		{
			die ($e->getMessage());
		}

		return $affected_rows;
	}

	public function GetCategory($id)
	{
		$this->row_item = array();

		try
		{
			$query = 	'SELECT category_id, permission, categories.visible' .
						' FROM ' . $this->table_name .
						' INNER JOIN categories ON categories.id = ' . $this->table_name . '.category_id' .
						' WHERE ' . $this->table_name . '.id = :id';
			
			$statement = $this->db->prepare($query);

			$statement->bindValue(':id', $id, PDO::PARAM_INT); 

			$statement->execute();
			
			$this->row_item = $statement->fetch(PDO::FETCH_ASSOC);			
		}
		catch (PDOException $e)
		{
			die ($e->getMessage());
		}

		return $this->row_item;
	}

	public function GetChildren($id)
	{
		$rows_result = array();

		try
		{
			$query = 	'SELECT caption, link FROM categories' .
						' WHERE parent_id = ' .
						' (SELECT category_id FROM ' . $this->table_name . ' WHERE id = :id)' .
						' ORDER BY item_order';

			$statement = $this->db->prepare($query);

			$statement->bindValue(':id', $id, PDO::PARAM_INT); 

			$statement->execute();
			
			$rows_result = $statement->fetchAll(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			die ($e->getMessage());
		}

		return $rows_result;
	}

	public function Comment($record)
	{
		if (!parent::check_required($record)) return NULL;

		$user_id = $record['user_id'];
		$page_id = $record['page_id'];
		$contents = strip_tags($record['contents']);
		$send_date = date("Y-m-d H:i:s");
		$visible = $record['visible'];

		$inserted_id = 0;

		try
		{
			$query = 	'INSERT INTO comments' .
						' (ip, user_id, page_id, comment_content, send_date, visible) VALUES' .
						' (:ip, :user_id, :page_id, :comment_content, :send_date, :visible)';

			$statement = $this->db->prepare($query);

			$statement->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR); 
			$statement->bindValue(':user_id', $user_id, PDO::PARAM_INT); 
			$statement->bindValue(':page_id', $page_id, PDO::PARAM_INT); 
			$statement->bindValue(':comment_content', $contents, PDO::PARAM_STR); 
			$statement->bindValue(':send_date', $send_date, PDO::PARAM_STR); 
			$statement->bindValue(':visible', $visible, PDO::PARAM_INT); 
			
			$statement->execute();

			$inserted_id = $this->db->lastInsertId();
		}
		catch (PDOException $e)
		{
			die ($e->getMessage());
		}

		return $inserted_id;
	}

	public function GetComments($id)
	{
		$rows_result = array();
		$visible = 1;

		try
		{
			$query = 	'SELECT ip, user_id, user_login, comment_content, send_date FROM comments' . 
						' INNER JOIN users ON users.id = comments.user_id' .
						' WHERE page_id = :id AND visible = :visible' .
						' ORDER BY comments.id';

			$statement = $this->db->prepare($query);

			$statement->bindValue(':id', $id, PDO::PARAM_INT); 
			$statement->bindValue(':visible', $visible, PDO::PARAM_INT); 

			$statement->execute();
			
			$rows_result = $statement->fetchAll(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			die ($e->getMessage());
		}

		return $rows_result;
	}
}

?>
