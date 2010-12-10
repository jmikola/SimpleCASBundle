<?php

namespace Bundle\SimpleCASBundle\Adapter\Doctrine\ODM;

use Bundle\SimpleCASBundle\Adapter\Adapter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Doctrine ODM MongoDB adapter.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class MongoDBAdapter implements Adapter
{
    /**
     * Document manager.
     *
     * @var Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $documentManager;

    /**
     * Document name for the user object.
     *
     * @var string
     */
    protected $documentName;

    /**
     * Field name for the principal identifier.
     *
     * @var string
     */
    protected $principalField;

    /**
     * MongoDBAdapter constructor.
     *
     * @param Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param array                                                     $options
     * @return MongoDBAdapter
     * @throws \InvalidArgumentException
     */
    public function __construct(ContainerInterface $container, array $options)
    {
        if (!isset($options['document_name'], $options['principal_field'])) {
            throw new \InvalidArgumentException('Missing required options: "document_name" and/or "principal_field"');
        }

        if (isset($options['document_manager'])) {
            $service = sprintf('doctrine.odm.mongodb.%s_document_manager', $options['document_manager']);
        } else {
            $service = 'doctrine.odm.mongodb.document_manager';
        }

        $this->documentManager = $container->get($service);
        $this->documentName = $options['document_name'];
        $this->principalField = $options['principal_field'];
    }

    /**
     * {@inheritdoc}
     */
    public function getUserByPrincipal($principal)
    {
        return $this->documentManager->getRepository($this->documentName)->findOneBy(array(
            $this->principalField => $principal,
        ));
    }
}
