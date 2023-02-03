<?php
// Для тестирования кейса из задания, необходимо только запустить файл
// Для тестирования других кейсов нужно заменить содержимое массива $flights 

$flights = [
  [
    'from'    => 'VKO',
    'to'      => 'DME',
    'depart'  => '01.01.2020 12:44',
    'arrival' => '01.01.2020 13:44',
  ],
  [
    'from'    => 'DME',
    'to'      => 'JFK',
    'depart'  => '02.01.2020 23:00',
    'arrival' => '03.01.2020 11:44',
  ],
  [
    'from'    => 'DME',
    'to'      => 'HKT',
    'depart'  => '01.01.2020 13:40',
    'arrival' => '01.01.2020 22:22',
  ],
  [
    'from'    => 'DME',
    'to'      => 'HKT',
    'depart'  => '01.01.2020 13:45',
    'arrival' => '05.01.2020 22:22',
  ],
];

class Node
{
  public $name;
  public $next = [];

  public function __construct(string $name) {
    $this->name = $name;
  }

  public function setNext(
    Node $node,
    string $depart,
    string $arrival
  ) {
    $this->next[] = [
      'node' => $node,
      'depart' => strtotime($depart),
      'arrival' => strtotime($arrival)
    ];
  }
}

class Graph 
{

  public $nodes = [];

  public function __construct($flights) {
    foreach ($flights as $flight) {
      if (!array_key_exists($flight['from'], $this->nodes)) {
        $this->nodes[$flight['from']] = new Node($flight['from']);
      }
      if (!array_key_exists($flight['to'], $this->nodes)) {
        $this->nodes[$flight['to']] = new Node($flight['to']);
      }
      $this->nodes[$flight['from']]->setNext(
        new Node($flight['to']), 
        $flight['depart'], 
        $flight['arrival']
      );
    }
  }

  public function getLongestRoute() { 
    $maxRoute = [
      'flights' => [],
      'duration' => 0
    ];   
    
    $all = [];
    foreach ($this->nodes as $node) {
      $nodeRoutes = $this->getAllRoutesByNode($node);
      $all = array_merge($all, $nodeRoutes);
    }

    foreach ($all as $route) {
      if ($route['duration'] > $maxRoute['duration']) {
        $maxRoute = $route;
      }
    }

    return $maxRoute;
  }

  public function getAllRoutesByNode(Node $node): array {  
    $routes = [];

    foreach ($node->next as $next) {
      $routes[] = [
          'flights' => [
            [
              'from' => $node, 
              'to' => $next['node'],
              'depart' => $next['depart'],
              'arrival' => $next['arrival']
            ]
          ],
          'duration' => $next['arrival'] - $next['depart']
      ];
    }

    do {
      $continue = false;
      foreach($routes as $key => $route) {
        $lastFlight = end($route['flights']);
        $lastflighNode = $this->nodes[$lastFlight['to']->name];

        $nextAvailable = array_filter($lastflighNode->next, function ($item) use ($lastFlight) {
          return ($item['depart'] - $lastFlight['arrival']) > 0;
        });
        
        if (count($nextAvailable) > 0) {
          $newRoutes = [];
          foreach ($nextAvailable as $next) {
            $newRoutes[] = [
              'flights' => array_merge($route['flights'], [
                  [
                    'from' => $lastflighNode, 
                    'to' => $next['node'],
                    'depart' => $next['depart'],
                    'arrival' => $next['arrival']
                  ]
                ]),
                'duration' => $route['duration'] + ($next['arrival'] - $next['depart'])
            ];
          }
          array_splice($routes, $key);
          $routes = array_merge($newRoutes, $routes);
        }
      }
    } while ($continue);

    return $routes;
  }

}

function getLongestRoute($flights) {
  $maxRoute = (new Graph($flights))->getLongestRoute();

  echo "Самый долгий маршрут: " . $maxRoute['flights'][0]['from']->name . " -> " . end($maxRoute['flights'])['to']->name . "\n";
  echo "Детализация маршрута: \n";

  foreach ($maxRoute['flights'] as $key => $flight) {
    $depart = date("Y-m-d H:i:s", $flight['depart']);
    $arrival = date("Y-m-d H:i:s", $flight['arrival']);
    echo ($key + 1) . ") " . $flight['from']->name . "->" . $flight['to']->name . " Посадка: " . $depart . " Приземление: " . $arrival . "\n";
  }
}

getLongestRoute($flights);