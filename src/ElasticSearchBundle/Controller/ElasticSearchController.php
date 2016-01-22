<?php

namespace ElasticSearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ElasticSearchController extends Controller
{
    public function searchAction(Request $request, $page)
    {
        $term = $request->get('q');

        if(strlen($term) === 0) {
            return $this->redirectToRoute('blog_index');
        }

        $finder = $this->container->get('fos_elastica.finder.app.post');

        $queryString = new \Elastica\Query\QueryString();
        $queryString->setDefaultField('_all');
        $queryString->setQuery($term);

        $query = new \Elastica\Query($queryString);
        $query->setSize(50);
        $query->setHighlight(array(
            'fields' => array('*' => new \stdClass)
        ));

        $elasticaSearchResults = $finder->findHybrid($query);

        $searchResults = array();
        $serializer = $this->get('serializer');
        foreach ($elasticaSearchResults as $elasticaSearchResult) {
            $resultJson = $serializer->serialize($elasticaSearchResult->getTransformed(), 'json');
            $resultObj =  json_decode($resultJson, true);
            foreach ($elasticaSearchResult->getResult()->getHit()['highlight'] as $key => $value) {
                if ($key !== 'slug') {
                    $resultObj[$key] = current($value);
                }
            }
            array_push($searchResults, $resultObj);
        }

        return $this->render('ElasticSearchBundle::elastica_search_results.html.twig', array(
            'term' => $term,
            'results' => $searchResults
        ));
    }
}
