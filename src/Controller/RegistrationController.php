<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use function Clue\StreamFilter\register;

class RegistrationController extends AbstractController
{

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {  
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            // encode the plain password
            $user->setPassword($passwordEncoder->encodePassword
            ($user, $form->get('plainPassword')->getData()));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
    *@Route("signin", name="signin" ) 
    */
    public function signIn(Request $request,UserPasswordEncoderInterface $passwordEncoder )
    {
        // $data = $request->request->all();
        $data = $request->getContent();
        $data = $request->toArray($data); // form data to array

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $form->submit($data);
        $form->isValid($data);

        if($request->getMethod() === 'POST'){
            
            $name = $data['name'];
            $user->setName($name);
            
            $email = $data['email'];
            $user->setEmail($email);

            $password = $data['password'];
            $user->setPassword($passwordEncoder->encodePassword($user, $password));
   
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse(['status' => 'User created!'], Response::HTTP_CREATED,);
        }else{
            return new JsonResponse(['status' => 'User not created!']);
        }   

        header('Access-Control-Allow-Origin: *');
        $JsonResponse = new JsonResponse();
        $JsonResponse->setData($user);

        return $JsonResponse;
    }
}
