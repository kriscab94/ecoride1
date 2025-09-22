<?php

namespace EcoRide\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private function createMailer(): PHPMailer {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'] ?? '';
        $mail->Password = $_ENV['MAIL_PASSWORD'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['MAIL_PORT'] ?? 587;
        
        // Recipients
        $mail->setFrom($_ENV['MAIL_FROM_EMAIL'] ?? 'noreply@ecoride.com', $_ENV['MAIL_FROM_NAME'] ?? 'EcoRide');
        
        return $mail;
    }
    
    public function sendVerificationEmail(string $email, string $token): void {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            
            $frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:3000';
            $verificationUrl = "$frontendUrl/verify-email?token=$token";
            
            $mail->isHTML(true);
            $mail->Subject = 'Vérifiez votre compte EcoRide';
            $mail->Body = $this->getVerificationEmailTemplate($verificationUrl);
            
            $mail->send();
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            throw new \Exception('Failed to send verification email');
        }
    }
    
    public function sendPasswordResetEmail(string $email, string $token): void {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            
            $frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:3000';
            $resetUrl = "$frontendUrl/reset-password?token=$token";
            
            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de mot de passe EcoRide';
            $mail->Body = $this->getPasswordResetEmailTemplate($resetUrl);
            
            $mail->send();
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            throw new \Exception('Failed to send password reset email');
        }
    }
    
    private function getVerificationEmailTemplate(string $verificationUrl): string {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #22c55e;'>🌱 Bienvenue sur EcoRide !</h2>
                <p>Merci de vous être inscrit sur notre plateforme de covoiturage écologique.</p>
                <p>Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>
                <a href='$verificationUrl' style='display: inline-block; background-color: #22c55e; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0;'>Vérifier mon email</a>
                <p>Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :</p>
                <p style='word-break: break-all; color: #666;'>$verificationUrl</p>
                <p>Ce lien expire dans 24 heures.</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='font-size: 12px; color: #888;'>L'équipe EcoRide - Voyagez écologique !</p>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getPasswordResetEmailTemplate(string $resetUrl): string {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #22c55e;'>🔐 Réinitialisation de mot de passe</h2>
                <p>Vous avez demandé une réinitialisation de votre mot de passe EcoRide.</p>
                <p>Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :</p>
                <a href='$resetUrl' style='display: inline-block; background-color: #22c55e; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0;'>Réinitialiser mon mot de passe</a>
                <p>Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :</p>
                <p style='word-break: break-all; color: #666;'>$resetUrl</p>
                <p>Ce lien expire dans 1 heure.</p>
                <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='font-size: 12px; color: #888;'>L'équipe EcoRide - Voyagez écologique !</p>
            </div>
        </body>
        </html>
        ";
    }
}