<?php
/**
 * Module Utilities
 * Management of the references data.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Utilities
 *

 * @license   Empty
 * @version   $Id: BannerImageObject.php 42 2011-06-02 00:54:28Z ssoares $
 */

/**
 * Manage data from references table.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_References
 *

 * @license   Empty
 * @version   $Id: BannerImageObject.php 42 2011-06-02 00:54:28Z ssoares $
 */
class BannerImageObject extends DataObject
{
    protected $_dataClass   = 'BannerImageData';

    protected $_indexClass      = 'BannerImageIndex';
    protected $_indexLanguageId = 'BII_LanguageID';

    protected $_constraint      = '';
    protected $_foreignKey      = 'BI_GroupID';

    public function imageCollection($id = 0)
    {
        (array) $array = array();

        if($id>0){
            $groups = $this->getAll(null,true,$id);
        }
        else {
            $groups = $this->getAll();
        }
        return $groups;
    }
}