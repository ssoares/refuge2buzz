<?php
/**
 * Module Utilities
 * Management of the featured elements.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Banners
 *

 * @license   Empty
 * @version   $Id: BannerFeaturedObject.php 134 2011-06-30 20:25:08Z ssoares $
 */

/**
 * Manage data from references table.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Banners
 *

 * @license   Empty
 * @version   $Id: BannerFeaturedObject.php 134 2011-06-30 20:25:08Z ssoares $
 */
class BannerFeaturedObject extends DataObject
{
    protected $_dataClass   = 'BannerFeaturedData';

    protected $_indexClass      = 'BannerFeaturedIndex';
    protected $_indexLanguageId = 'BFI_LanguageID';

    protected $_constraint      = '';
    protected $_foreignKey      = '';

    public function loadData($recordID, $langId)
    {
        $oBannerImgFeat = new BannerFeaturedImageObject();
        
        $record  = $this->populate($recordID, $langId);
        $tmpData = $oBannerImgFeat->getData($langId, $recordID);
        
        foreach ($tmpData as $imgData)
        {
            foreach ($imgData as $key => $value)
            {
                $recordKey = $key . $imgData['IF_ImgID'];
                $record[$recordKey] = $value;
            }
        }
        
        return $record;
    }
}