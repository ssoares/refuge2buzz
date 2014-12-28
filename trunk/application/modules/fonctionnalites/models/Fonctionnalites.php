<?php

class Fonctionnalites extends Zend_Db_Table
{
    protected $_name = 'Fonctionnalites';

     /**
     * Select data from database.
     *
     * @param int $id   The form id if defined.
     * @param int $lang The language id if defined.
     *
     * @return object $select List of froms from DB.
     */
    public function getEquipeList($id = null, $lang = null)
    {
        $db = new EquipeManagement();
        $select = $db->select()
            ->from($this->_name)
            ->setIntegrityCheck(false)
            ->join('Equipe_EquipeManagementIndex', 'Equipe_EquipeManagement.EEM_ID = Equipe_EquipenManagementIndex.EEMI_EquipeManagementID');

        if ($id)
        {
            $select->where('Equipe_EquipeManagement.EEM_ID = ?', $id);
        }
        if ($lang)
        {
            $select->where('Equipe_EquipeManagementIndex.EEMI_LanguageID = ?', $lang);
        }
        else
        {
            $select->where('Equipe_EquipeManagementIndex.EEMI_LanguageID = ?',
                    Zend_Registry::get('languageID'));
        }

        return $select;
    }
    
    /**
     * Build an array with all the data needed to display the form foe edition
     *
     * @param int $id   Id of the form to display
     * @param int $lang Id of the current language displayed
     *
     * @return array $data An array with all the data retrieved from linked tables
     */
    public function getEquipeDetails($id, $lang)
    {
       
    }
}

?>