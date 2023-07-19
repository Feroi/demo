<?php

namespace App\Controller;

use App\Entity\Provider;
use App\Form\ProviderType;
use App\Repository\ProviderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @Route("admin/provider")
 */
class ProviderController extends AbstractController
{
    /**
     * @Route("/", name="app_provider_index", methods={"GET"})
     */
    public function index(ProviderRepository $providerRepository): Response
    {
        return $this->render('provider/index.html.twig', [
            'providers' => $providerRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_provider_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ProviderRepository $providerRepository, HttpClientInterface $client): Response
    {
        $allStates = $this->getStates($client);

        $allCities = $this->getCities($client, '09');


        $provider = new Provider();
        $form = $this->createForm(ProviderType::class, $provider);
        $form
            ->add('state', ChoiceType::class, [
                'label' => 'Estado',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 20
                ],
                'choices' => $allStates,
                'data' => '09'
            ])
        ;
        $form
            ->add('city', ChoiceType::class, [
                'label' => 'Ciudad',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 20
                ],
                'choices' => $allCities
            ])
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $providerRepository->add($provider);
            return $this->redirectToRoute('app_provider_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('provider/new.html.twig', [
            'provider' => $provider,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/get-cities", name="app_provider_cities", methods={"GET", "POST"})
     */
    public function getCitiesRequest(Request $request, ProviderRepository $providerRepository, HttpClientInterface $client)
    {
        $state = $request->query->get('state', null);
        $allCities = $this->getCities($client, $state);
        
        return new JsonResponse($allCities);
    }

    /**
     * @Route("/{id}", name="app_provider_show", methods={"GET"})
     */
    public function show(Provider $provider, HttpClientInterface $client): Response
    {
        $state = $provider->getState();
        $city = $provider->getCity();

        $data = $this->showData($client, $state, $city);

        return $this->render('provider/show.html.twig', [
            'provider' => $provider,
            'state' => $data['state'],
            'city' => $data['ciudad']
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_provider_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Provider $provider, ProviderRepository $providerRepository, HttpClientInterface $client): Response
    {
        
        $allStates = $this->getStates($client);

        $allCities = $this->getCities($client, $provider->getState());

        $form = $this->createForm(ProviderType::class, $provider);

        $form
            ->add('state', ChoiceType::class, [
                'label' => 'Estado',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 20
                ],
                'choices' => $allStates,
            ])
        ;
        $form
            ->add('city', ChoiceType::class, [
                'label' => 'Ciudad',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 20
                ],
                'choices' => $allCities
            ])
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $providerRepository->add($provider);
            return $this->redirectToRoute('app_provider_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('provider/edit.html.twig', [
            'provider' => $provider,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_provider_delete", methods={"POST"})
     */
    public function delete(Request $request, Provider $provider, ProviderRepository $providerRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$provider->getId(), $request->request->get('_token'))) {
            $providerRepository->remove($provider);
        }

        return $this->redirectToRoute('app_provider_index', [], Response::HTTP_SEE_OTHER);
    }

    public function getStates($client){
        //Get states
        $statesRequest = $client->request(
            'GET',
            'https://api.tau.com.mx/dipomex/v1/estados',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'APIKEY' => '482ff6ce6d64d60f619ab1719f80e1e65b0406ef'
                    ],
            ]
        );

        $states = $statesRequest->toArray();

        $allStates = [];
        foreach($states['estados'] as $state){
            $allStates[$state['ESTADO']] = $state['ESTADO_ID'];
        }
        
        return $allStates;

    }

    public function getCities($client, $state){
        //Get first cities
        $citiesRequest = $client->request(
            'GET',
            'https://api.tau.com.mx/dipomex/v1/municipios?id_estado=' . $state,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'APIKEY' => '482ff6ce6d64d60f619ab1719f80e1e65b0406ef'
                    ],
            ]
        );

        $cities = $citiesRequest->toArray();

        $allCities = [];
        foreach($cities['municipios'] as $city){
            $allCities[$city['MUNICIPIO']] = $city['MUNICIPIO_ID'];
        }
        
        return $allCities;
    }

    public function showData($client, $state, $city) {
        
        //Get states
        $stateRequest = $client->request(
            'GET',
            'https://api.tau.com.mx/dipomex/v1/estado?id='.$state,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'APIKEY' => '482ff6ce6d64d60f619ab1719f80e1e65b0406ef'
                ],
            ]
        );

        $states = $stateRequest->toArray();

        $state = '';
        $stateId = '';
        foreach ($states['estado'] as $st) {
            $state = $st['ESTADO'];
            $stateId = $st['ESTADO_ID'];
        }

        //Get first cities
        $citiesRequest = $client->request(
            'GET',
            'https://api.tau.com.mx/dipomex/v1/municipios?id_estado='. $stateId,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'APIKEY' => '482ff6ce6d64d60f619ab1719f80e1e65b0406ef'
                ],
            ]
        );

        $cities = $citiesRequest->toArray();


        $ciudad = '';
        foreach ($cities['municipios'] as $ci) {
            if ($ci['MUNICIPIO_ID'] == $city) {
                $ciudad = $ci['MUNICIPIO'];
            }
        }

        return [
            'state' => $state,
            'stateId' => $stateId,
            'ciudad' => $ciudad
        ];
    }
}
