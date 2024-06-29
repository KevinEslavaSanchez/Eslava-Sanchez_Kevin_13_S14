<?php
namespace RefactoringGuru\Strategy\Conceptual;

// Clases y lógica PHP aquí
class Context
{
    private $strategy;

    public function __construct(Strategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function setStrategy(Strategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function doSomeBusinessLogic(): void
    {
        echo "Contexto: Ordenando datos usando la estrategia (no estoy seguro de cómo lo hará)<br>";
        $result = $this->strategy->doAlgorithm(["a", "b", "c", "d", "e"]);
        echo implode(",", $result) . "<br>";
    }
}

interface Strategy
{
    public function doAlgorithm(array $data): array;
}

class ConcreteStrategyA implements Strategy
{
    public function doAlgorithm(array $data): array
    {
        sort($data);
        return $data;
    }
}

class ConcreteStrategyB implements Strategy
{
    public function doAlgorithm(array $data): array
    {
        rsort($data);
        return $data;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejemplo de Patrón Strategy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        h1 {
            text-align: center;
        }
        .output {
            background: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Ejemplo de Patrón Strategy</h1>

        <div class="output">
            <?php
            // Uso del patrón Strategy
            $context = new Context(new ConcreteStrategyA());
            echo "Cliente: Estrategia establecida para ordenamiento normal.<br>";
            $context->doSomeBusinessLogic();

            echo "<br>";

            echo "Cliente: Estrategia establecida para ordenamiento inverso.<br>";
            $context->setStrategy(new ConcreteStrategyB());
            $context->doSomeBusinessLogic();
            ?>
        </div>
    </div>
</body>
</html>
