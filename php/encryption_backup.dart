import 'dart:convert';
import 'dart:typed_data';
import 'package:crypto/crypto.dart'; // لتوليد مفتاح التشفير
import 'package:encrypt/encrypt.dart'; // مكتبة للتشفير وفك التشفير

class Encryption {
  static final String _ivKey = "99754f94d3106633"; // IV ثابت (مطابق لـ PHP)
  static final String _password = "StrongKey12345"; // كلمة المرور لتوليد المفتاح
// طول المفتاح 256 بت

  // توليد مفتاح التشفير باستخدام SHA256
  static Key _generateKey(String password) {
    final hash = sha256.convert(utf8.encode(password));
    return Key(Uint8List.fromList(hash.bytes));
  }

  // تشفير النص
  static String encryptText(String plaintext) {
    final iv = IV.fromUtf8(_ivKey); // استخدام نفس IV
    final key = _generateKey(_password); // توليد المفتاح
    final encrypter = Encrypter(AES(key, mode: AESMode.cbc)); // تهيئة AES

    // تشفير النص وتحويله إلى Base64
    final encrypted = encrypter.encrypt(plaintext, iv: iv);
    return encrypted.base64;
  }

  // فك التشفير
  static String decryptText(String encryptedText) {
    final iv = IV.fromUtf8(_ivKey); // استخدام نفس IV
    final key = _generateKey(_password); // توليد المفتاح
    final encrypter = Encrypter(AES(key, mode: AESMode.cbc)); // تهيئة AES

    try {
      // فك النص المشفر
      final decrypted = encrypter.decrypt(Encrypted.fromBase64(encryptedText), iv: iv);
      return decrypted;
    } catch (e) {
      print("Error during decryption: $e");
      return "Decryption error!";
    }
  }
}

void main() {
  // نص للاختبار
  String encryptedText = "Hr3dauNuAjHuIXaKbuXx5Q=="; // النص المشفر من PHP

  // فك التشفير
  String decryptedText = Encryption.decryptText(encryptedText);
  print("Decrypted Text: $decryptedText");
}
