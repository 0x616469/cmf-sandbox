<?php

namespace Sandbox\MagnoliaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * A controller to demo interaction with magnolia
 *
 * @author alain.horner@liip.ch
 * @author david.buchmann@liip.ch
 * @author smith@pooteeweet.org
 */
class MagnoliaController extends Controller
{
    /**
     * Read an article from magnolia. Try for example about/subsection-articles/an-interesting-article
     *
     * @param string $article the repository path in magnolia, starting after /demo-project/
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function magnoliaArticleAction($article)
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

                // if the following line fails, try commenting it out and activating the one below instead
                // dbu had problems getting the identifier to resolve. the commented line references an image by path
                $imgNode = $dms->getNodeByIdentifier($properties['imageDmsUUID'])->getNode('document');
                // $imgNode = $dms->getNode('/demo-project/img/bk/Opener/an-array-of-multiple-lights-in-holiday-season-fashion')->getNode('document');

                $img = $imgNode->getPropertiesValues();
                $img['jcr:data'] = base64_encode(stream_get_contents($imgNode->getProperty('jcr:data')->getBinary()));

                return $this->render('SandboxMagnoliaBundle:Magnolia:magnoliaArticle.html.twig', array(
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

    /**
     * Show editing a value in the jackrabbit of magnolia: edit the site slogan
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function magnoliaEditAction()
    {
        $path = '/demo-project';

        $session = $this->get('doctrine_phpcr')->getConnection('website');
        $node = $session->getNode($path);

        $content = $node->getPropertyValue('slogan');

        return $this->render('SandboxMagnoliaBundle:Magnolia:magnoliaEdit.html.twig', array(
            'title' => 'Edit magnolia',
            'page'  => array(
                'tags' => array("hulla", "holla"),
                'path' => $path,
                'title' => 'Edit the magnolia slogan',
                'content' => $content
            )
        ));
    }

    /**
     * Handle the callback by create.js and write the edited content piece.
     *
     * @param string $id the path at which to edit
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function magnoliaWriteAction($id)
    {
        $params = $this->getRequest()->request->all();

        $session = $this->get('doctrine_phpcr')->getConnection('website');
        $node = $session->getNode('/' . $id);

        $node->setProperty('slogan', strip_tags($params['<http://rdfs.org/sioc/ns#content>']));
        $session->save();

        return new \Symfony\Component\HttpFoundation\Response($params['<http://rdfs.org/sioc/ns#content>']);
    }


    /**
     * Fetch an article with the ODM to demo the DocumentClassMapper
     *
     * @return \Symfony\Component\HttpFoundation\Response the response
     */
    public function magnoliaOdmAction()
    {
        $dm = $this->container->get('doctrine_phpcr.odm.website_document_manager');

        $doc = $dm->find(null, '/demo-project/about/subsection-articles');

        return $this->render('SandboxMagnoliaBundle:Magnolia:magnoliaOdm.html.twig', array(
            'section' => $doc,
            'title' => $doc->title,
        ));
    }

}
