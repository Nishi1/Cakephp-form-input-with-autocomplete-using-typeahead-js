<html>
<?php echo $this->Html->css("tagmanager.min.css");?>
<?php echo $this->Html->script(["tagmanager.min.js","bootstrap3-typeahead.min.js"]);?>
<form class="form-horizontal" role="form" action="/admin/hashtag_subscriptions/add" method="post">                    
  <div class="form-group">
		<label for="subscribers" class="col-sm-2 control-label">Users</label>
		<div class="col-sm-10">                    
		  <input type="text" name="subscribers" id="subscribers" placeholder="Type User name" class="typeahead tm-input form-control tm-input-info tt-empty"  placeholder="Search Users"/>
		  <input type="hidden" id="hashtag_id" name="hashtag_id" value="<?php echo $hashtag->id; ?>" /> 
		  <input type="hidden" class="sub_ids" name="sub_ids" value="" />                           
		</div>
  </div>                                
  <div class="form-group">
	<div class="col-sm-offset-2 col-sm-10">
		<button type="button" class="btn btn-default"
		data-dismiss="modal">
			Close
		</button>
	  <button type="submit" class="btn btn-primary">Submit</button>
	</div>
  </div>
</form> 

<script>
$(document).ready(function() {    
	var tagApi = $(".tm-input").tagsManager();
	jQuery(".typeahead").typeahead({	
	  source: function (query, process) {		
		 $.get('<?php echo \Cake\Routing\Router::url(['controller' => 'HashtagSubscriptions', 'action' => 'autocomplete']);?>', { query: query, hashtag_id: $('#hashtag_id').val()  }, function (data) {
		  data = $.parseJSON(data);
		  return process(data);				  		 
		});
	  }, 		 
	  afterSelect :function (item){				      
		tagApi.tagsManager("pushTag", item.id);       
	  }
	});   
});
</script>
</html> 

<?php
//PHP code to return user data:
	public function autocomplete() {		
		$this->autoRender = false;
		$this->loadModel('Users');
		$connection = ConnectionManager::get('default');
		$terms = $connection->execute('select users.id, users.name from users where name LIKE "%'. trim($this->request->query['query']) .'%" and id not in (select user_id from hashtag_subscriptions where hashtag_id='.$this->request->query('hashtag_id').') Limit 10');
		$terms = $terms->fetchAll('assoc');
		echo json_encode($terms);		
	}
	
//PHP code to save autocomplete data:
//We can also optimize below code in better way

if ($this->request->is('post') && !empty($this->request->getData('hashtag_id') && !empty($this->request->getData('hidden-subscribers')))) {
	$this->loadModel("Users");	
	$hashtag_id = $this->request->getData('hashtag_id');		
	$arr = explode(',',$this->request->getData('hidden-subscribers'));			
	foreach($arr as $k=>$user_id) {			
		$new_arr = ['user_id' => $user_id, 'hashtag_id' => $hashtag_id];
		$hashtagSubscription = $this->HashtagSubscriptions->newEntity();					
		$hashtagSubscription = $this->HashtagSubscriptions->patchEntity($hashtagSubscription, $new_arr); 
		$this->HashtagSubscriptions->save($hashtagSubscription);	
	} 	
	return $this->redirect(['action' => 'index',$this->request->getData('hashtag_id')]);           
} 
?>
