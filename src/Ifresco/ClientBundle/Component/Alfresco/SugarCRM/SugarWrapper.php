<?php
namespace Ifresco\ClientBundle\Component\Alfresco\SugarCRM;

class SugarWrapper extends SugarREST {
    public function __construct($restUrl,$username,$password,$md5_password=true) {
        parent::__construct($restUrl,$username,$password,$md5_password);
    }   
    
    /**
    * Function:    findByField($module, $fields, $byField, $searchValue, $exact, $options)
    * Parameters:     $module    = (string) the SugarCRM module name. Usually first
    *            letter capitalized. This is the name of the base
    *            module. In other words, any other modules involved
    *            in the query will be related to the given base
    *            module.
    *        $fields        = (array) the fields you want to retrieve: 
    *                array(
    *                    'field_name',
    *                    'some_other_field_name'
    *                )
    *        $byField    = (string) field to search in
    *        $searchValue   = (string) search for the value in the field
    *        $exact     = (boolean) exact search or like
    *        $options    = (array)[optional] Lets you set options for the query:
    *                $options['limit'] = Limit how many records returned
    *                $options['offset'] = Query offset
    *                $options['order_by'] = ORDER BY clause for an SQL statement
    * Description:    Retrieves Sugar Bean records. Essentially returns the result of a
    *        SELECT SQL statement. 
    * Returns:    A 2-D array, first dimension is records, second is fields. For instance, the
    *        'name' field in the first record would be accessed in $result[0]['name].
    */
    public function findByField($module, $fields, $byField, $searchValue, $exact=true, $options=null) {
        $searchStr = "=";
        if ($exact == false)
            $searchStr = " LIKE ";
             
        $optionsNew = array("where"=>" ".strtolower($module).".{$byField}{$searchStr}'{$searchValue}'");
        
        if ($options != null)
            $options = array_merge($optionsNew,$options);
        else
            $options = $optionsNew;
            
        return parent::get($module,$fields,$options);  
    }
    
    /**
    * Function:    findById($module, $fields, $id, $options)
    * Parameters:     $module    = (string) the SugarCRM module name. Usually first
    *            letter capitalized. This is the name of the base
    *            module. In other words, any other modules involved
    *            in the query will be related to the given base
    *            module.
    *        $fields        = (array) the fields you want to retrieve: 
    *                array(
    *                    'field_name',
    *                    'some_other_field_name'
    *                )
    *        $id   = (string) bean id
    *        $options    = (array)[optional] Lets you set options for the query:
    *                $options['limit'] = Limit how many records returned
    *                $options['offset'] = Query offset
    *                $options['order_by'] = ORDER BY clause for an SQL statement
    * Description:    Retrieves Sugar Bean records. Essentially returns the result of a
    *        SELECT SQL statement. 
    * Returns:    A 2-D array, first dimension is records, second is fields. For instance, the
    *        'name' field in the first record would be accessed in $result[0]['name].
    */
    public function findById($module, $fields, $id, $options=null) {
        return $this->findByField($module,$fields,"id",$id,true,$options);
    }     
}