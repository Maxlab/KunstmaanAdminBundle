<?php

namespace Kunstmaan\AdminBundle\Controller;

use Doctrine\ORM\EntityManager;

use Kunstmaan\AdminBundle\Entity\User;
use Kunstmaan\AdminBundle\Form\UserType;
use Kunstmaan\AdminBundle\AdminList\UserAdminListConfigurator;
use Kunstmaan\AdminListBundle\AdminList\AdminList;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use FOS\UserBundle\Util\UserManipulator;

/**
 * Settings controller handling everything related to creating, editing, deleting and listing users in an admin list
 */
class UsersController extends BaseSettingsController
{
    /**
     * List users
     *
     * @Route("/", name="KunstmaanAdminBundle_settings_users")
     * @Template("KunstmaanAdminListBundle:Default:list.html.twig")
     *
     * @throws AccessDeniedException
     * @return array
     */
    public function usersAction()
    {
        $this->checkPermission();

        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        /* @var AdminList $adminList */
        $adminList = $this->get("kunstmaan_adminlist.factory")->createList(new UserAdminListConfigurator($em));
        $adminList->bindRequest($request);

        return array(
            'adminlist' => $adminList,
        );
    }

    /**
     * Add a user
     *
     * @Route("/add", name="KunstmaanAdminBundle_settings_users_add")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @throws AccessDeniedException
     * @return array
     */
    public function addUserAction()
    {
        $this->checkPermission();

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $user = new User();
        $form = $this->createForm(new UserType(), $user, array('password_required' => true, 'validation_groups' => array('Registration')));

        if ('POST' == $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();

                /* @var UserManipulator $manipulator */
                $manipulator = $this->get('fos_user.util.user_manipulator');
                $manipulator->changePassword($user->getUsername(), $user->getPlainpassword());

                $this->get('session')->getFlashBag()->add('success', 'User \''.$user->getUsername().'\' has been created!');

                return new RedirectResponse($this->generateUrl('KunstmaanAdminBundle_settings_users'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Edit a user
     *
     * @param int $id
     *
     * @Route("/{id}/edit", requirements={"id" = "\d+"}, name="KunstmaanAdminBundle_settings_users_edit")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @throws AccessDeniedException
     * @return array
     */
    public function editUserAction($id)
    {
        $this->checkPermission();

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        /* @var User $user */
        $user = $em->getRepository('KunstmaanAdminBundle:User')->find($id);

        $form = $this->createForm(new UserType(), $user, array('password_required' => false));

        if ('POST' == $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                if ($user->getPlainpassword() != "") {
                    $manipulator = $this->get('fos_user.util.user_manipulator');
                    $manipulator->changePassword($user->getUsername(), $user->getPlainpassword());
                }
                $user->setPlainpassword("");
                $em->persist($user);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'User \''.$user->getUsername().'\' has been edited!');

                return new RedirectResponse($this->generateUrl('KunstmaanAdminBundle_settings_users'));
            }
        }

        return array(
            'form' => $form->createView(),
            'user' => $user
        );
    }

    /**
     * Delete a user
     *
     * @param int $id
     *
     * @Route("/{id}/delete", requirements={"id" = "\d+"}, name="KunstmaanAdminBundle_settings_users_delete")
     * @Method({"GET", "POST"})
     *
     * @throws AccessDeniedException
     * @return array
     */
    public function deleteUserAction($id)
    {
        $this->checkPermission();

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();
        /* @var User $user */
        $user = $em->getRepository('KunstmaanAdminBundle:User')->find($id);
        if (!is_null($user)) {
            $username = $user->getUsername();
            $em->remove($user);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'User \''.$username.'\' has been deleted!');
        }

        return new RedirectResponse($this->generateUrl('KunstmaanAdminBundle_settings_users'));
    }

}