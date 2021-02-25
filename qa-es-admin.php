<?php

require_once dirname(__FILE__) . '/es-client/client.php';

class qa_elasticsearch {
	private $es_client;
	private $es_hostname;
	private $es_port;
	private $es_scheme;
	private $es_username;
	private $es_password;
	private $es_index_name;
	private $es_enabled;

	function create_es_client_if_needed() {
		$this->es_enabled = qa_opt('elasticsearch_enabled');
		if ( $this->es_enabled && !$this->es_client) {
			$this->es_hostname = qa_opt('elasticsearch_hostname');
			$this->es_port = qa_opt('elasticsearch_port');
			$this->es_scheme = qa_opt('elasticsearch_scheme');
			$this->es_username = qa_opt('elasticsearch_username');
			$this->es_password = qa_opt('elasticsearch_password');
			$this->es_index_name = qa_opt('elasticsearch_index_name');
			$this->es_client = create_es_client(
				$this->es_hostname, 
				$this->es_port,
				$this->es_scheme, 
				$this->es_username,
				$this->es_password
			);
			$params = array( 'index' => $this->es_index_name);
			if ( !$this->es_client->indices()->exists($params))
				$this->es_client->indices()->create($params);
		}
	}
	
	function allow_template($template)
	{
		return ($template!='admin');
	}
	
	function option_default($option) {
		switch($option) {
			case 'elasticsearch_hostname':
				return 'localhost';
			case 'elasticsearch_port':
				return '9200';
			case 'elasticsearch_index_name':
				return 'q2a-elasticsearch';
			case 'elasticsearch_scheme':
				return 'http';
			case 'elasticsearch_enabled':
				return false;
			case 'elasticsearch_username':
				return null;
			case 'elasticsearch_password':
				return null;
			default:
				return null;
		}

	}
	function admin_form(&$qa_content)
	{
		//      Process form input
		$ok = null;
		if (qa_clicked('accept_save_button')) {
			$is_es_enabled = (bool)qa_post_text('elasticsearch_enabled');
			qa_opt('elasticsearch_hostname',qa_post_text('elasticsearch_hostname'));
			qa_opt('elasticsearch_port',qa_post_text('elasticsearch_port'));
			qa_opt('elasticsearch_scheme',qa_post_text('elasticsearch_scheme'));
			qa_opt('elasticsearch_username',qa_post_text('elasticsearch_username'));
			qa_opt('elasticsearch_password',qa_post_text('elasticsearch_password'));
			qa_opt('elasticsearch_index_name',qa_post_text('elasticsearch_index_name'));
			qa_opt('elasticsearch_enabled',$is_es_enabled);
			if ( $is_es_enabled ) { 
			   qa_opt('search_module', 'qa_elasticsearch');	
			}
			$ok = qa_lang('admin/options_saved');
		}
		else if (qa_clicked('accept_reset_button')) {
			foreach($_POST as $i => $v) {
				$def = $this->option_default($i);
				if($def !== null) qa_opt($i,$def);
			}
			qa_opt('search_module', '');	
			$ok = qa_lang('admin/options_reset');
		}

		//      Create the form for display

		$fields = array();

		$fields[] = array(
				'label' => 'Enable ElasticSearch',
				'tags' => 'NAME="elasticsearch_enabled"',
				'value' => qa_opt('elasticsearch_enabled'),
				'type' => 'checkbox',
				);
		$fields[] = array(
				'label' => 'ElasticSearch Hostname',
				'tags' => 'NAME="elasticsearch_hostname"',
				'value' => qa_opt('elasticsearch_hostname'),
				'type' => 'input',
				);
		$fields[] = array(
				'label' => 'ElasticSearch Port',
				'tags' => 'NAME="elasticsearch_port"',
				'value' => qa_opt('elasticsearch_port'),
				'type' => 'input',
				);
		$fields[] = array(
				'label' => 'ElasticSearch Scheme',
				'tags' => 'NAME="elasticsearch_scheme"',
				'value' => qa_opt('elasticsearch_scheme'),
				'type' => 'input',
				);
		$fields[] = array(
					'label' => 'ElasticSearch Username',
					'tags' => 'NAME="elasticsearch_username"',
					'value' => qa_opt('elasticsearch_username'),
					'type' => 'input',
				);
		$fields[] = array(
					'label' => 'ElasticSearch Password',
					'tags' => 'NAME="elasticsearch_password" type="password"',
					'value' => qa_opt('elasticsearch_password'),
					'type' => 'input',
				);
		$fields[] = array(
				'label' => 'Index Name',
				'tags' => 'NAME="elasticsearch_index_name"',
				'value' => qa_opt('elasticsearch_index_name'),
				'type' => 'input',
				);
		return array(
				'ok' => ($ok && !isset($error)) ? $ok : null,

				'fields' => $fields,

				'buttons' => array(
					array(
						'label' => qa_lang_html('main/save_button'),
						'tags' => 'NAME="accept_save_button"',
					     ),
					array(
						'label' => qa_lang_html('admin/reset_options_button'),
						'tags' => 'NAME="accept_reset_button"',
					     ),
					),
			    );
	}

	public function index_post($postid, $type, $questionid, $parentid, $title, $content, $format, $text, $tagstring, $categoryid) {
		//$start = microtime(true);
		require_once QA_INCLUDE_DIR.'app/posts.php';
		$this->create_es_client_if_needed();
		$params = array();
		if ($type === "A") { //If this is an answer
			$query = qa_db_read_one_assoc(qa_db_query_sub("
		SELECT userid, title, created, updated, catidpath1, catidpath2, catidpath3 FROM ^posts WHERE `postid` = #", $questionid), true);
			$userid = $query['userid'];
			$title = $query['title'];
			$catidpath1 = $query['catidpath1'];
			$catidpath2 = $query['catidpath2'];
			$catidpath3 = $query['catidpath3'];
			$created = $query['created'];
			$updated = $query['updated'];
		} else {
			$query = qa_db_read_one_assoc(qa_db_query_sub("
		SELECT userid, selchildid, created, updated, catidpath1, amaxvote, catidpath2, catidpath3 FROM ^posts WHERE `postid` = #", $questionid), true);
			$userid = $query['userid'];
			$selchildid = $query['selchildid'];
			$catidpath1 = $query['catidpath1'];
			$catidpath2 = $query['catidpath2'];
			$catidpath3 = $query['catidpath3'];
			$amaxvote = $query['amaxvote'];
			$created = $query['created'];
			$updated = $query['updated'];
		}
		//error_log(json_encode($query));
		//error_log((json_encode(qa_post_get_full($questionid))));
		$params['body']  = array(
		     'questionid' => $questionid,
		     'title' => $title,
		     'content' => $content,
		     'format' => $format,
		     'text' => $text,
		     'tagstring' => $tagstring,
		     'categoryid' => $categoryid,
			 'userid' => $userid,
			 'created' => $created,
			 'updated' => $updated,
			 'catidpath1' => $catidpath1,
			 'catidpath2' => $catidpath2,
			 'catidpath3' => $catidpath3,
		     'parentid' => $parentid,
		     'postid' => $postid,
		     'type' => $type	
		);
		if ($type === "Q") {
			if (isset($selchildid) && !is_null($selchildid)) {
			$query = qa_db_read_one_assoc(qa_db_query_sub("
	SELECT content FROM ^posts WHERE `postid` = #", $selchildid), true);
			$params['body']['selchildcontent'] = $query['content'];
			$params['body']['selchildtext'] =  qa_post_content_to_text($query['content'], $format);
			$params['body']['selchildid'] = $selchildid;
			} else {
				$query = qa_db_read_one_assoc(qa_db_query_raw("
	SELECT postid, content FROM qa_posts WHERE `parentid` = ".$postid." AND `netvotes` = ".$amaxvote." LIMIT 1"), true);
			$params['body']['selchildcontent'] = $query['content'];
			$params['body']['selchildtext'] =  qa_post_content_to_text($query['content'], $format);
			$params['body']['selchildid'] = $query['postid'];
			}
		}

		$params['index'] = $this->es_index_name;
		$params['id']    = intval($postid);
		//error_log("Executed index");
		// Document will be indexed to my_index/my_type/my_id
		$ret = $this->es_client->index($params);
		//error_log(microtime(true) - $start);
	}

	public function unindex_post($postid) {
		$this->create_es_client_if_needed();
		$deleteParams = array();
		$deleteParams['index'] = $this->es_index_name;
		$deleteParams['id'] = $postid;
		//error_log("Executed unindex");
		if ( $this->es_client->exists($deleteParams)) 
			$retDelete = $this->es_client->delete($deleteParams);
	}

	public function move_post($postid, $categoryid) {
		$this->create_es_client_if_needed();
		$params = array();
		$params['index'] = $this->es_index_name;
                $params['type']  = 'post';
                $params['id']    = $postid;
		//error_log("Executed move");
		if ( $this->es_client->exists($params)) {
		   $params['body']['doc'] = array ('categoryid' => $categoryid);
		   $this->es_client->update($params);	
		}	
	}

	public function process_search($query, $start, $count, $userid, $absoluteurls, $fullcontent) {
		$this->create_es_client_if_needed();
		$results = array();
		$params['index'] = $this->es_index_name;
		$params['body']['query']['multi_match'] = array ( 'query' => $query , 'fields' => array('title','content','text'));
        $params['from'] = $start;
		$params['size'] = $count;
		$params['body']['collapse']['field'] = 'questionid';
		$es_results = $this->es_client->search($params);

		$total_found = $es_results['hits']['total'];

		foreach ( $es_results['hits']['hits'] as $q) {
			$question = $q['_source'];
			$results[]=array(
                                'question_postid' => intval($question['questionid']),
                                'match_type' => strval($question['type']),
                                'match_postid' => intval($question['postid']),
				'title' => $question['title']
                        );
		}
		return $results;
	}
	
}
