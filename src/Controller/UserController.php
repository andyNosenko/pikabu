<?php /** @noinspection PhpUndefinedVariableInspection */

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegisterType;
use App\Form\UpdateUserAdminAccessType;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError();
        $lastUserName = $utils->getLastUsername();
        return $this->render('user/index.html.twig', [
            'error' => $error,
            'last_username' => $lastUserName,
        ]);
    }

    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {

        $user = new Users();

        $user->setRoles(['ROLE_USER']);
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('login');
        }
        return $this->render('register/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

//    /**
//     * @param Users $user
//     * @return Response
//     * @Route("/profile/{user}", name="profile")
//     */
//    public function profile(AuthorizationCheckerInterface $authChecker, Users $user): Response
//    {
//        $a = $this->get('security.token_storage')->getToken()->getUser();
//        dump($a->getId()); exit;
//
//        if (false === $authChecker->isGranted("IS_AUTHENTICATED_FULLY")) {
//            throw new AccessDeniedException('Unable to access this page!');
//        }
//        return $this->render('user/profile.html.twig', [
//            'user' => $user,
//        ]);
//    }

    /**
     * @param Users $user
     * @return Response
     * @Route("/profile", name="profile")
     */
    public function profile(AuthorizationCheckerInterface $authChecker): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (false === $authChecker->isGranted("IS_AUTHENTICATED_FULLY")) {
            throw new AccessDeniedException('Unable to access this page!');
        }
        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

//    /**
//     * @param Request $request
//     * @param Users $user
//     * @return Response
//     * @Route("/profile/update/{user}", name="update_profile")
//     */
//    public function updateUser(Request $request,
//                               AuthorizationCheckerInterface $authChecker,
//                               UserPasswordEncoderInterface $passwordEncoder,
//                               Users $user)
//    {
//        if (false === $authChecker->isGranted("IS_AUTHENTICATED_FULLY")) {
//            throw new AccessDeniedException('Unable to access this page!');
//        }
//        $form = $this->createForm(RegisterType::class, $user, [
//            'action' => $this->generateUrl('update_profile', [
//                'user' => $user->getId()
//            ]),
//            'method' => 'POST',
//        ]);
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $users = $form->getData();
//            $users->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));
//            $em = $this->getDoctrine()->getManager();
//            $em->flush();
//            return $this->render('user/profile.html.twig', [
//                'user' => $users,
//            ]);
//        }
//        return $this->render('register/form.html.twig', [
//            'form' => $form->createView()
//        ]);
//    }

    /**
     * @param Request $request
     * @param Users $user
     * @return Response
     * @Route("/profile/update", name="update_profile")
     */
    public function updateUser(Request $request,
                               AuthorizationCheckerInterface $authChecker,
                               UserPasswordEncoderInterface $passwordEncoder)
    {
        if (false === $authChecker->isGranted("IS_AUTHENTICATED_FULLY")) {
            throw new AccessDeniedException('Unable to access this page!');
        }
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $form = $this->createForm(RegisterType::class, $user, [
            'action' => $this->generateUrl('update_profile', [
                'user' => $user->getId()
            ]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $users = $form->getData();
            $users->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->render('user/profile.html.twig', [
                'user' => $users,
            ]);
        }
        return $this->render('register/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param Users $user
     * @return Response
     * @Route("/edit/user/{id}", name="edit_user")
     */
    public function editUser(Request $request,
                             AuthorizationCheckerInterface $authChecker,
                             UserPasswordEncoderInterface $passwordEncoder,
                             Users $user)
    {
        if (false === $authChecker->isGranted("IS_AUTHENTICATED_FULLY")) {
            throw new AccessDeniedException('Unable to access this page!');
        }
        $form = $this->createForm(UpdateUserAdminAccessType::class, $user, [
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $users = $form->getData();
            $users->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->render('user/profile.html.twig', [
                'user' => $users,
            ]);
        }
        return $this->render('user/update_admin/update.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/user/delete/{id}", name="delete_user")
     */
    public function deleteUser(Request $request,
                               AuthorizationCheckerInterface $authChecker,
                               Users $user)
    {
        if (false === $authChecker->isGranted("IS_AUTHENTICATED_FULLY")) {
            throw new AccessDeniedException('Unable to access this page!');
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('articles');
    }

    /**
     * @Route("/administration/users", name="administration_users")
     * @param Request $request
     * @param UserService $query
     * @return Response
     */
    public function administrationUsers(Request $request, UserService $query)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if ($user->getRoles()[0] === "ROLE_MODERATOR") {
            $users = $query->ReturnUsersAndBloggers($request);
        }

        if ($user->getRoles()[0] === "ROLE_ADMIN") {
            $users = $query->ReturnAllUsers($request);
        }
        return $this->render('user/users.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/make_blogger/{id}", name="make_blogger")
     * @param Request $request
     * @param UserService $query
     * @param Users $user
     * @return Response
     */
    public function makeBlogger(Request $request, UserService $query, Users $user)
    {
        $user->setRoles(['ROLE_BLOGGER']);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        $users = $query->ReturnUsersAndBloggers($request);
        return $this->render('user/users.html.twig', [
            'users' => $users,
        ]);

    }

    /**
     * @Route("/make_user/{id}", name="make_user")
     * @param Users $user
     * @return Response
     */
    public function makeUser(Request $request, UserService $query, Users $user)
    {
        $user->setRoles(['ROLE_USER']);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        $users = $query->ReturnUsersAndBloggers($request);

        return $this->render('user/users.html.twig', [
            'users' => $users,
        ]);

    }


    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {

    }
}
