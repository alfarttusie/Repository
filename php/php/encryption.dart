import 'dart:convert';
import 'dart:typed_data';
import 'package:crypto/crypto.dart';
import 'package:encrypt/encrypt.dart';

class Encryption {
  static final String _ivKey = "8941cfef779497da";
  static final String _password = "password";

  static Key _generateKey(String password) {
    final hash = sha256.convert(utf8.encode(password));
    return Key(Uint8List.fromList(hash.bytes));
  }

  static String decryptText(String encryptedText) {
    final iv = IV.fromUtf8(_ivKey);
    final key = _generateKey(_password);
    final encrypter = Encrypter(AES(key, mode: AESMode.cbc));

    try {
      final decrypted =
          encrypter.decrypt(Encrypted.fromBase64(encryptedText), iv: iv);
      return decrypted;
    } catch (e) {
      print("❌ Decryption failed: $e");
      return "Error";
    }
  }
}

void main() {
  // ضع هنا النص المشفر من PHP
  String encryptedText = "JahMh+rHSoyKNWIRPKIZ0A==";
  String decrypted = Encryption.decryptText(encryptedText);
  print("Decrypted: $decrypted");
}
