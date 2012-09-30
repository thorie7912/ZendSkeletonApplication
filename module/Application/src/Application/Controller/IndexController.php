<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Form\Login as LoginForm;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $viewModel = new ViewModel();
        $viewModel->form = new LoginForm();

        $serviceManager = $this->getServiceLocator();

        $authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
        $identity = $authService->getIdentity();
        if ($identity) {
            return array('email' => $identity);
        }
        return array('loggedIn' => false, 'form' => new LoginForm());
    }

    public function loginAction()
    {

        $form = new LoginForm();

        $request = $this->getRequest();

        if ($request->isPost()) {

            $form->setData($request->getPost());

            if ($form->isValid()) {

                $email = $form->get('email')->getValue();
                $password = $form->get('password')->getValue();

                $serviceManager = $this->getServiceLocator();

                $authAdapter = $serviceManager->get('Zend\Authentication\Adapter\DbTable');
                $authAdapter->setTableName('users')
                    ->setIdentityColumn('email')
                    ->setCredentialColumn('password')
                    ->setCredentialTreatment('PASSWORD(?)')
                    ->setIdentity($email)
                    ->setCredential($password);

                $select = $authAdapter->getDbSelect();
                $select->where('status != "deleted"');

                $authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
                $result = $authService->authenticate($authAdapter);


                if ($result->isValid()) {
                    // Login successful, redirect to stats overview page
                    return $this->redirect()->toRoute('home');
                } else {
                    $form->get('email')->setMessages($result->getMessages());
                }

            }

        }

        return array('form' => $form);
    }

    public function logoutAction() {
        $serviceManager = $this->getServiceLocator();
        $authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
        $authService->clearIdentity();
        return $this->redirect()->toRoute('home');
    }
}
