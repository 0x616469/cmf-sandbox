<?php

namespace Sandbox\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TestController extends Controller
{
    public function indexAction()
    {
        return $this->render('SandboxTestBundle:Test:index.html.twig', array('title'=>'Normal Symfony Route'));
    }

    public function magnoliaAction ()
    {
        $path = '/demo-project/about/subsection-articles/large-article/content';

        $session = $this->get('doctrine_phpcr')->getConnection('website');
        $node = $session->getNode($path);
        $content = $node->getPropertyValue('phpcr');

        return $this->render('SandboxTestBundle:Test:magnolia.html.twig', array(
            'title'=>'Normal Symfony Route',
            'page' => array(
                'tags' => array("hulla", "holla"),
                'path' => $path,
                'title' => 'mytitle',
                'content' => $content
            )
        ));
    }

    public function magnoliaWriteAction ($contentPath)
    {
        $params = $this->getRequest()->request->all();

        $session = $this->get('doctrine_phpcr')->getConnection('website');
        $node = $session->getNode('/' . $contentPath);

        $node->setProperty('phpcr', $params['<http://rdfs.org/sioc/ns#content>']);
        $session->save();

        return new \Symfony\Component\HttpFoundation\Response();
    }

}
