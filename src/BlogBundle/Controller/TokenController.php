<?php

namespace BlogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use BlogBundle\Form\Type\CredentialType;
use BlogBundle\Exception\InvalidFormException;

/**
 * @FOSRest\NamePrefix("api_")
 */
class TokenController extends FOSRestController
{
    /**
     * Get the user's token.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "BlogBundle\Form\Type\CredentialType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has error",
     *     401 = "Returned when the credentials are not valid"
     *   }
     * )
     *
     * @FOSRest\View
     *
     * @param Request $request the request object
     *
     * @return array
     */
    public function postTokensAction(Request $request)
    {
        try {
            $credentials = $request->request->all();
            $token = $this->processForm($credentials);
            $routeOptions = [
                '_format' => $request->get('_format'),
            ];

            return $this->routeRedirectView('api_get_articles', [], Response::HTTP_OK, $token);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        } catch (\Exception $exception) {
            return $this->view(['error' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Processes the form.
     *
     * @param CredentialInterface $credentials
     * @param array               $parameters
     * @param String              $method
     *
     * @return array
     *
     * @throws InvalidFormException
     */
    private function processForm(array $parameters, $method = 'POST')
    {
        $trans = $this->get('translator');
        $user_manager = $this->get('fos_user.user_manager');
        $encoder_factory = $this->get('security.encoder_factory');

        $form = $this->createForm(new CredentialType(), [], ['method' => $method]);
        $form->submit($parameters, true);

        if ($form->isValid()) {
            $credentials = $form->getData();

            $user = $user_manager->loadUserByUsername($credentials['username']);
            if (!$user) {
                throw new \Exception($trans->trans('blog_bundle.bad_credentials'));
            }

            $encoder = $encoder_factory->getEncoder($user);
            if (!$encoder->isPasswordValid($user->getPassword(), $credentials['password'], $user->getSalt())) {
                throw new \Exception($trans->trans('blog_bundle.bad_credentials'));
            }

            return ['X-Auth-Token' => $user->getApiKey()];
        }
        throw new InvalidFormException($trans->trans('blog_bundle.bad_credentials'), $form);
    }
}
