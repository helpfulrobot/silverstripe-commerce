<?php

class Product extends DataObject {
	public static $db = array(
		'Title'			=> 'Varchar',
		'URLSegment'	=> 'Varchar',
		'Price'         => 'Decimal',
		'Description'	=> 'HTMLText',
        'Sort'          => 'Int',
		'Quantity'		=> 'Int',
		'PackSize'      => 'Varchar',
		'Weight'        => 'Int',
		'StockID'		=> 'Varchar(99)'
	);
	
	public static $has_many = array(
		'Images'		    => 'ProductImage',
		'Attributes'        => 'ProductAttribute',
		'Customisations'    => 'ProductCustomisation'
	);
	
	public static $belongs_many_many = array(
		'Categories'	=> 'ProductCategory'
	);
	
	public static $casting = array(
	    'CategoriesList'    => 'Varchar',
	    'Thumbnail'         => 'Varchar'
	);
	
	public static $summary_fields = array(
        'Thumbnail'     => 'Thumbnail',
	    'Title'         => 'Title',
	    'URLSegment'    => 'URL Segment',
	    'StockID'       => 'Stock Number', 
	    'Price'         => 'Price',
	    'CategoriesList'=> 'Categories'
	);

    public static $default_sort = "\"Sort\" DESC";
	
	/**
     * Return a URL to link to this product (via Catalog_Controller)
     * 
     * @return string URL to cart controller
     */
    public function Link(){
        if(Controller::curr()->request->Param('ID'))
            $cat_url = Controller::curr()->request->Param('ID');
        elseif($this->Categories()->First())
            $cat_url = $this->Categories()->First()->URLSegment;
        else
            $cat_url = 'product';
        
        return Controller::join_links(BASE_URL , $this->URLSegment);
    }
    
    /**
     * Overwrite default image and load a not found image if not found
     *
     */
    public function getImages() {    
        if($this->Images()->exists())
            $images = $this->Images();
        else {
            $images = new Image();
            $images->Title = "No Image Available";
            $images->FileName = BASE_URL . '/commerce/images/no-image.png';
        }
        
        return $images;
    }
    
    public function getThumbnail() {
        if($this->Images()->exists())
            return $this->Images()->first()->CMSThumbnail();
        else
            return '(No Image)';
    }
    
    /**
     * Determin if the product has more than one image
     *
     * return Boolean
     */
    public function HasMultipleImages() {
        if($this->Images()->exists() && $this->Images()->count() > 1)
            return true;
        else
            return false;
    }
    
	public function getCategoriesList() {
	    $list = '';
	    
	    if($this->Categories()->exists()){
	        foreach($this->Categories() as $category) {
	            $list .= $category->Title;
	            $list .= ', ';
	        }
	    }
	    
	    return $list;
	}
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		// Use to display autogenerated URL
		$url_field = TextField::create('URLSegment')
		    ->setReadonly(true)
		    ->performReadonlyTransformation();
		
		$fields->addFieldToTab('Root.Main', $url_field, 'Price');
        $fields->addFieldToTab('Root.Main', HTMLEditorField::create('Description')->setRows(20)->addExtraClass('stacked'));
        
		$fields->removeByName('Sort');
		$fields->removeByName('Quantity');
		$fields->removeByName('PackSize');
		$fields->removeByName('Weight');
		$fields->removeByName('StockID');
		
		$additional_field = ToggleCompositeField::create('AdditionalData', 'Additional Data',
			array(
				NumericField::create("Quantity", $this->fieldLabel('Quantity')),
				TextField::create("Sort", $this->fieldLabel('Sort')),
				TextField::create("PackSize", $this->fieldLabel('PackSize')),
				TextField::create("Weight", $this->fieldLabel('Weight')),
				TextField::create("StockID", $this->fieldLabel('StockID'))
			)
		)->setHeadingLevel(4);
		
		$fields->addFieldToTab('Root.Main', $additional_field);
		
		if($this->ID) {
		    // Deal with product images
		    $upload_field = new UploadField('Images');
		    $upload_field->setFolderName('products');
		
		    $fields->addFieldToTab('Root.Images', $upload_field);
		
		    // Deal with product features
		    $attributes_field = new StackedTableField('Attributes', 'ProductAttribute', null, array('Title' => 'TextField', 'Content' => 'TextField'));
		
		    $fields->addFieldToTab('Root.Attributes', $attributes_field);
		    
		    // Deal with customisations
            $add_button = new GridFieldAddNewButton('toolbar-header-left');
            $add_button->setButtonName('Add Customisation');
            
		    $custom_config = GridFieldConfig::create()->addComponents(
                new GridFieldToolbarHeader(),
                $add_button,
                new GridFieldSortableHeader(),
                new GridFieldDataColumns(),
                new GridFieldPaginator(20),
                new GridFieldEditButton(),
                new GridFieldDeleteAction(),
                new GridFieldDetailForm()
            );
		    $custom_field = GridField::create('Customisations', '', $this->Customisations(), $custom_config);
		    $fields->addFieldToTab('Root.Customisations', $custom_field);
		}
		
        $this->extend('updateCMSFields', $fields);
        
		return $fields;
	}
	
	public function onBeforeWrite() {
	    parent::onBeforeWrite();
	    
	    // Only call on first creation, ir if title is changed
	    if(($this->ID == 0) || $this->isChanged('Title') || !($this->URLSegment)) {
	        // Set the URL Segment, so it can be accessed via the controller
            $filter = URLSegmentFilter::create();
		    $t = $filter->filter($this->Title);
		
		    // Fallback to generic name if path is empty (= no valid, convertable characters)
		    if(!$t || $t == '-' || $t == '-1') $t = "category-{$this->ID}";
	        
	        // Ensure that this object has a non-conflicting URLSegment value.
	        $existing_cats = ProductCategory::get()->filter('URLSegment',$t)->count();
	        $existing_products = Product::get()->filter('URLSegment',$t)->count();
	        $existing_pages = (class_exists('SiteTree')) ? SiteTree::get()->filter('URLSegment',$t)->count() : 0;
	        
	        $count = (int)$existing_cats + (int)$existing_products + (int)$existing_pages;
	        
	        $this->URLSegment = ($count) ? $t . '-' . ($count + 1) : $t;
	    }
	}
	
    public function onBeforeDelete() {
        // Delete all attributes when this opbect is deleted    
        foreach($this->Attributes() as $attribute) {
            $attribute->delete();
        }
        
        // Delete all customisations when this opbect is deleted    
        foreach($this->Customisations() as $cusomisation) {
            $cusomisation->delete();
        }
        
        parent::onBeforeDelete();
    }
	
    public function canView($member = false) {
        return true;
    }

    public function canCreate($member = null) {
        return true;
    }

    public function canEdit($member = null) {
        return true;
    }

    public function canDelete($member = null) {
        return true;
    }
}
