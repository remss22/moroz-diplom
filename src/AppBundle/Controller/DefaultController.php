<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Data;
use AppBundle\Entity\Sensor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Repository;


class DefaultController extends Controller
{
    /**
     * @var Repository\SensorRepository
     */
    private $_sensorRepository;

    /**
     * @var Repository\DataRepository
     */
    private $_dataRepository;

    private function _getRepository() {
        $this->_sensorRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Sensor');
        $this->_dataRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Data');
    }

    /**
     * @Route("/", name="show")
     */
    public function showAction(Request $request)
    {
        $this->_getRepository();
        $data = ['data' => $this->_sensorRepository->getSensors([])];

        return $this->render('index.html.twig', $data);
    }

    /**
     * @Route("/add", name="add")
     */
    public function addAction(Request $request)
    {
        return $this->render('add.html.twig');
    }

    /**
     * @Route("/add-sensor", name="add-sensor")
     */
    public function addSensorAction(Request $request)
    {
        $name = $request->get('name');
        $max  = $request->get('max');
        $min  = $request->get('min');
        $this->_getRepository();
        $em = $this->getDoctrine()->getManager();

        if ($this->_sensorRepository->getSensorByFilter(['name' => $name])) {
            return new JsonResponse(['status' => "error", 'message' => "Датчик с таким названием уже существует"]);
        }
        if (!is_numeric($max) || !is_numeric($min)) {
            return new JsonResponse(['status' => "error", 'message' => "Некорректные занчения max или min"]);
        }
        try {
            $sensor = new Sensor();
            $sensor->setName(addslashes($name));
            $sensor->setMaxValue(addslashes($max));
            $sensor->setMinValue(addslashes($min));
            $sensor->setHash(md5($sensor->getName()));
            $em->persist($sensor);
            $em->flush();
        } catch (\Throwable $t) {
            return new JsonResponse(['status' => "error", 'message' => "Неизвестная ошибка"]);
        }

        return new JsonResponse(['status' => "ok"]);
    }

    /**
     * @Route("/monitoring/{hash}", name="monitoring", requirements={"hash": "\w+"})
     */
    public function monitoringAction(Request $request, $hash)
    {
        $this->_getRepository();
        $sensor = $this->_sensorRepository->getSensorByFilter(['hash' => $hash]);

        return $this->render('monitoring.html.twig', ['hash' => $sensor->getHash(), 'max' => $sensor->getMaxValue(), 'min' => $sensor->getMinValue(), 'name' => $sensor->getName()]);

    }

    /**
     * @Route("/monitoring/{hash}/getData", name="getData", requirements={"hash": "\w+"})
     */
    public function monitoringDataAction(Request $request, $hash)
    {
        $this->_getRepository();
        $data = $this->_dataRepository->getDataByFilter(['hash' => $hash]);
        $result = [];

        foreach ($data as $dat) {
            $result[] = $dat->getValue();

        }
        return new JsonResponse(['data' => $result]);
    }

    /**
     * @Route("addData/{hash}", name="addData", requirements={"hash": "\w+"})
     */
    public function addDataAction(Request $request, $hash)
    {
        $this->_getRepository();
        $em = $this->getDoctrine()->getManager();

        $data = new Data();
        $data->setValue(1);
        $a = 100;
        while ($a !== 0) {
            $data = new Data();
            $data->setValue($a);
            $data->setHash($hash);
            $data->setCreationDate(new \DateTime());
            $a--;
            $em->persist($data);
        }
        $em->flush();
        return new JsonResponse(['status' => 'ok']);
    }

    /**
     * @Route("/delete/{hash}", name="delete", requirements={"hash": "\w+"})
     */
    public function deleteAction(Request $request, $hash)
    {
        $this->_getRepository();
        $em = $this->getDoctrine()->getManager();
        $sensor = $em->getRepository('AppBundle:Sensor')->findBy(['hash' => $hash]);
        $em->remove($sensor[0]);
        $em->flush();

        $this->_getRepository();
        return $this->redirectToRoute('show');

    }
}
