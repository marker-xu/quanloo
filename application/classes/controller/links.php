<?php 
/**
 * 
 * 友情链接
 * @author xucongbin
 *
 */
class Controller_Links extends Controller {
	
	public function action_index() {
	    $objModelLink = new Model_Data_Link();
	    $arrText = array( );
	    $arrImage = array( );
	    $query = array();
	    $sort = array("sort_no"=>1);
	    $arrList = $objModelLink->find($query, array(), $sort);
	    
		if($arrList) {
			foreach($arrList as $row) {
				if($row['type']==Model_Data_Link::TYPE_IMAGE) {
					$arrImage[] = $row;
				}  else {
					$arrText[] = $row;
				}
			}
		}
	    $this->template->set("text_list", $arrText);
	    $this->template->set("image_list", $arrImage);
	}
}
