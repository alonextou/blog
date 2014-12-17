<?php

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.modelitem');
 
class Jw_findstoreModelJw_findstore extends JModelItem
{
        protected $filters;
        protected $collections;
 
        public function getFilters() 
        {
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query->select('id,title,image,ordering');
                $query->from('#__jw_findstore_categories');
                $query->where('state = 1');
                $query->order('ordering ASC');
                $db->setQuery((string)$query);
                $categories = $db->loadObjectList();

                foreach ($categories as $category){
                        $db = JFactory::getDBO();
                        $query = $db->getQuery(true);
                        $query->select('id,title,collections,category,uris,ordering');
                        $query->from('#__jw_findstore_mappings');
                        $query->where('state = 1');
                        $query->where('category = '.$category->id);
                        $query->order('ordering ASC');
                        $db->setQuery((string)$query);
                        $mappings = $db->loadObjectList();
                        $filters[] = array($category->id => $category->title, "image" => $category->image, "mappings" => $mappings);
                }

                return $filters;
        }

        public function getCollections() 
        {
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query->select('id,title,col_id,ordering');
                $query->from('#__jw_findstore_collections');
                $query->where('state = 1');
                $query->order('ordering ASC');
                $db->setQuery((string)$query);
                $collections = $db->loadObjectList();

                //$collections = json_encode($collections);
                return $collections;
        }

        public function getMappings() 
        {
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query->select('id,collections,category, uris');
                $query->from('#__jw_findstore_mappings');
                $query->where('state = 1');
                $db->setQuery((string)$query);
                $mappings = $db->loadObjectList();

                return $mappings;
        }

        public function getUris() 
        {
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query->select('id,collections,category,uris');
                $query->from('#__jw_findstore_mappings');
                $query->where('state = 1');
                $db->setQuery((string)$query);
                $mappings = $db->loadObjectList();

                foreach ($mappings as $mapping){
                        $db = JFactory::getDBO();
                        $query = $db->getQuery(true);
                        $query->select('id,title,collections,category,ordering');
                        $query->from('#__jw_findstore_mappings');
                        $query->where('state = 1');
                        $query->where('category = '.$category->id);
                        $query->order('ordering ASC');
                        $db->setQuery((string)$query);
                        $mappings = $db->loadObjectList();
                        $filters[] = array($category->id => $category->title, "image" => $category->image, "mappings" => $mappings);
                }

                return $filters;
        }

}