<?php

namespace Sandbox\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TestController extends Controller
{
    public function indexAction()
    {
        return $this->render('SandboxTestBundle:Test:index.html.twig', array('title'=>'Normal Symfony Route'));
    }

    public function magnoliaAction($article)
    {
        $website = $this->get('doctrine_phpcr')->getConnection('website');

        $node = $website->getNode("/demo-project/$article");

        $dms = $this->get('doctrine_phpcr')->getConnection('dms');

        $template = $node->getNode('MetaData')->getPropertyValue('mgnl:template');

        switch ($template) {
            case "standard-templating-kit:pages/stkSection":
                break;
            case "standard-templating-kit:pages/stkLargeArticle":
            case "standard-templating-kit:pages/stkArticle":
                $subnode = $node->getNode("content");
                $subnode->setProperty('phpcr', 'was here!!');
                $website->save();
                $properties = $subnode->getNode('00')->getPropertiesValues();

                $imgNode = $dms->getNodeByIdentifier($properties['imageDmsUUID'])->getNode('document');
                $img = $imgNode->getPropertiesValues();
                $img['jcr:data'] = base64_encode(stream_get_contents($imgNode->getProperty('jcr:data')->getBinary()));

                return $this->render('SandboxTestBundle:Test:magnolia.html.twig', array(
                        'node' => $node,
                        'article' => $node->getPropertiesValues(),
                        'content' => $properties,
                        'title' => 'foo',
                        'img' => $img,
                    )
                );
        }

        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Could not map content');
    }
}
