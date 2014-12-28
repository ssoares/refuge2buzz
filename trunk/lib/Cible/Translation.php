<?php
/**
 * Cible Framework
 *
 *
 * @category   Cible
 * @package    Cible_Translate
 * @copyright
 * @license
 * @version
 */


/**
 * @package    Cible_Translation
 * @copyright
 * @license
 */
abstract class Cible_Translation
{

    /**
     * Standard frontends
     *
     * @var array
     */
    //public static $standardFrontends = array('Core', 'Output', 'Class', 'File', 'Function', 'Page');

    /**
     * Consts for clean() method
     */
    const TRANSLATION_TYPE_CIBLE = 1;
    const TRANSLATION_TYPE_CLIENT = 2;


    public static function __($key, $type, $lang = null, $replace = array()){

        $registry = Zend_Registry::getInstance();
        $cache = $registry->get('cache');
        $db = $registry->get('db');

        $type = ( $type == self::TRANSLATION_TYPE_CIBLE ) ? 'cible' : 'client';

        $lang = empty($lang) ? $registry->get('languageID') : $lang;

        $identifier = $key . '_' . $lang;

        if (!($data = $cache->load($identifier))) {
            $qry = $db->select()
                ->from('Static_Texts', 'ST_Value')
                ->where('ST_Identifier = ?', $key)
                ->where('ST_Type = ?', $type)
                ->where('ST_langID = ?', $lang);
            $data = $db->fetchOne($qry);

            if(!empty($data)){
                $tags = array('staticTexts', $type);
                $cache->save($data, $identifier, $tags);
            } else {
                $data = $identifier . ' not found in database';
            }
        }
        if (!empty($replace)){
            foreach ($replace as $key => $value){
                $search[] = $key;
                $replaceValues[] = $value;
            }
            $data = str_replace($search, $replaceValues, $data);
        }
        if( Zend_Registry::get('enableDictionnary') == 'true' ){
            $template = "<span id='$key'>%TEXT%</span><a href=\"javascript:dictionnary_edit('$key', '$type','$lang');\">[e]</a>";
            return str_replace('%TEXT%', $data, $template);
        } else {
            return $data;
        }
    }

    public static function set($key, $type, $value, $lang = null){

        $registry = Zend_Registry::getInstance();
        $cache = $registry->get('cache');
        $db = $registry->get('db');

        $type = ( $type == 'cible' ) ? 'cible' : 'client';

        $lang = empty($lang) ? $registry->get('languageID') : $lang;

        $identifier = $key . '_' . $lang;

        $data = $db->fetchOne("SELECT ST_Value FROM Static_Texts WHERE ST_Identifier = '$key' AND ST_langID = '$lang' AND ST_Type = '$type'");
        $tags = array('staticTexts', $type);

        if (!$data ) {

            $data = array(
                'ST_Identifier' => $key,
                'ST_LangID' => $lang,
                'ST_Value' => $value,
                'ST_Type' =>  $type
            );

            $db->query("INSERT INTO Static_Texts (ST_Identifier,ST_LangID,ST_Value,ST_Type) VALUES ('$key','$lang',?,'$type')", $value);

        } else {

            $db->query("UPDATE Static_Texts SET ST_Value = ? WHERE ST_Identifier = '$key' AND ST_LangID = '$lang' AND ST_Type = '$type'", $value);
        }

        $cache->save($value, $identifier, $tags);

    }

    public static function getClientText($key, $lang = null)
    {
        return self::__($key, self::TRANSLATION_TYPE_CLIENT, $lang);
    }

    public static function getCibleText($key, $lang = null)
    {
        return self::__($key, self::TRANSLATION_TYPE_CIBLE, $lang);
    }

}
