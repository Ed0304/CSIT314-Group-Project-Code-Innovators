<?php

class AccessLoanCalculatorPage {
    public function AccessLoanCalculatorUI() {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Loan Calculator</title>
            <style>
                /* Reset some default styling */
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                /* Center the content */
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f9;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                }

                /* Container Styling */
                .container {
                    text-align: center;
                    width: 100%;
                    max-width: 400px;
                    background-color: #ffffff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }

                /* Heading */
                h1 {
                    margin-bottom: 20px;
                    color: #333;
                }

                /* Form Styling */
                form {
                    margin-bottom: 15px;
                }

                label {
                    font-size: 14px;
                    color: #555;
                    display: block;
                    margin-top: 10px;
                    margin-bottom: 5px;
                    text-align: left;
                }

                input[type="text"] {
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    font-size: 14px;
                }

                button {
                    width: 100%;
                    padding: 10px;
                    margin-top: 15px;
                    background-color: #28a745;
                    color: #ffffff;
                    border: none;
                    border-radius: 4px;
                    font-size: 16px;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                }

                /* Hover effect for button */
                button:hover {
                    background-color: #218838;
                }

                /* Back button styling */
                .back-button {
                    background-color: #007bff;
                    margin-top: 10px;
                }

                .back-button:hover {
                    background-color: #0069d9;
                }

                /* Result display */
                .result {
                    margin-top: 20px;
                    font-size: 18px;
                    color: #333;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Loan Calculator</h1>
                <form method="post">
                    <label for="amount">Loan Amount:</label>
                    <input type="text" name="amount" id="amount" required>
                    
                    <label for="interest">Interest Rate (%):</label>
                    <input type="text" name="interest" id="interest" required>
                    
                    <label for="years">Loan Term (Years):</label>
                    <input type="text" name="years" id="years" required>
                    
                    <button type="submit">Calculate</button>
                </form>
                
                <form action="buyer_dashboard.php">
                    <button type="submit" class="back-button">Back</button>
                </form>
                
                <?php
                if (isset($_POST['amount']) && isset($_POST['interest']) && isset($_POST['years'])) {
                    $controller = new AccessLoanCalculatorController();
                    $monthlyPayment = $controller->calculateLoan(
                        $_POST['amount'], 
                        $_POST['interest'], 
                        $_POST['years']
                    );
                    echo "<div class='result'>Monthly Payment: $" . number_format($monthlyPayment, 2) . "</div>";
                }
                ?>
            </div>
        </body>
        </html>
        <?php
    }
}

class AccessLoanCalculatorController {
    public function calculateLoan($amount, $interest, $years) {
        $loan = new LoanCalculator($amount, $interest, $years);
        return $this->calculateMonthlyPayment($loan);
    }

    private function calculateMonthlyPayment($loan) {
        $interest = $loan->getInterest() / 100;
        $monthlyInterest = $interest / 12;
        $months = $loan->getYears() * 12;
        return $loan->getAmount() * ($monthlyInterest * pow(1 + $monthlyInterest, $months)) /
               (pow(1 + $monthlyInterest, $months) - 1);
    }
}

class LoanCalculator {
    private $amount;
    private $interest;
    private $years;

    public function __construct($amount, $interest, $years) {
        $this->amount = $amount;
        $this->interest = $interest;
        $this->years = $years;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getInterest() {
        return $this->interest;
    }

    public function getYears() {
        return $this->years;
    }
}

$loanCalculatorUI = new AccessLoanCalculatorPage();
$loanCalculatorUI->AccessLoanCalculatorUI();

?>
