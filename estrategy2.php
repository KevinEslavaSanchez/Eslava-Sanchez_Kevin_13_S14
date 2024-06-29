<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estrategia - Ejemplo PHP</title>
</head>
<body>
    <h1>Estrategia - Ejemplo en PHP</h1>

    <?php
    // Definición de las interfaces y clases
    interface Strategy {
        public function execute($a, $b);
    }

    class ConcreteStrategyAdd implements Strategy {
        public function execute($a, $b) {
            return $a + $b;
        }
    }

    class ConcreteStrategySubtract implements Strategy {
        public function execute($a, $b) {
            return $a - $b;
        }
    }

    class ConcreteStrategyMultiply implements Strategy {
        public function execute($a, $b) {
            return $a * $b;
        }
    }

    class Context {
        private $strategy;

        public function setStrategy(Strategy $strategy) {
            $this->strategy = $strategy;
        }

        public function executeStrategy($a, $b) {
            return $this->strategy->execute($a, $b);
        }
    }

    // Procesamiento de formulario
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $numero1 = $_POST["numero1"];
        $numero2 = $_POST["numero2"];

        // Crear una instancia de Context
        $context = new Context();

        // Establecer la estrategia para suma y ejecutar
        $context->setStrategy(new ConcreteStrategyAdd());
        $resultadoSuma = $context->executeStrategy($numero1, $numero2);

        // Establecer la estrategia para resta y ejecutar
        $context->setStrategy(new ConcreteStrategySubtract());
        $resultadoResta = $context->executeStrategy($numero1, $numero2);

        // Establecer la estrategia para multiplicación y ejecutar
        $context->setStrategy(new ConcreteStrategyMultiply());
        $resultadoMultiplicacion = $context->executeStrategy($numero1, $numero2);

        // Mostrar resultados
        echo "<h2>Resultados:</h2>";
        echo "<p>Suma de $numero1 y $numero2: $resultadoSuma</p>";
        echo "<p>Resta de $numero1 y $numero2: $resultadoResta</p>";
        echo "<p>Multiplicación de $numero1 y $numero2: $resultadoMultiplicacion</p>";
    }
    ?>

    <!-- Formulario para ingresar números -->
    <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
        <label for="numero1">Número 1:</label>
        <input type="number" id="numero1" name="numero1" required>
        <br><br>
        <label for="numero2">Número 2:</label>
        <input type="number" id="numero2" name="numero2" required>
        <br><br>
        <button type="submit">Calcular</button>
    </form>
</body>
</html>
