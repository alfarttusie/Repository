import 'dart:convert';
import 'dart:typed_data';
import 'package:crypto/crypto.dart'; 
import 'package:encrypt/encrypt.dart'; 

class Encryption {
  static final String _ivKey = "99754f94d3106633"; 
  static final String _password = "StrongKey12345"; 

  static Key _generateKey(String password) {
    final hash = sha256.convert(utf8.encode(password));
    return Key(Uint8List.fromList(hash.bytes));
  }

  static String encryptText(String plaintext) {
    final iv = IV.fromUtf8(_ivKey);
    final key = _generateKey(_password);
    final encrypter = Encrypter(AES(key, mode: AESMode.cbc));

    final encrypted = encrypter.encrypt(plaintext, iv: iv);
    return encrypted.base64;
  }

  static String decryptText(String encryptedText) {
    final iv = IV.fromUtf8(_ivKey); 
    final key = _generateKey(_password);
    final encrypter = Encrypter(AES(key, mode: AESMode.cbc));

    try {
      final decrypted = encrypter.decrypt(Encrypted.fromBase64(encryptedText), iv: iv);
      return decrypted;
    } catch (e) {
      print("Error during decryption: $e");
      return "Decryption error!";
    }
  }
}

void main() {
  String encryptedText = "hLlHBf1fTevAgF13K8oIbQ=="; 
  String decryptedText = Encryption.decryptText(encryptedText);
  print("Decrypted Text: $decryptedText");
  print(Encryption.encryptText("iraq man from dart"));
}
