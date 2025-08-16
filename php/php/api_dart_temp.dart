import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  static const String baseUrl = 'https://pass.alfarttusie.com/php/requests.php';
  static String? bearerToken;
  static Map<String, String> _defaultHeaders() {
    return {
      'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
      'Content-Type': 'application/json',
      if (bearerToken != null) 'Bearer': bearerToken!,
    };
  }

  static Future<http.Response?> sednRequest(Map<String, dynamic> body) async {
    final url = Uri.parse(baseUrl);
    try {
      final response = await http.post(
        url,
        headers: _defaultHeaders(),
        body: jsonEncode(body),
      );
      ApiService.bearerToken = response.headers['bearer'];
      return response;
    } catch (e) {
      return null;
    }
  }
}
//repository
// void main() async {
//   final initsession = await ApiService.sendRequest({'type': 'init session'});
//   if (initsession != null) {
//     if (jsonDecode(initsession.body)['status'] == 'successful') {
//       print("initsession : Ok !");
//       final response = await ApiService.sendRequest({
//         'type': 'sign in',
//         "username": "alfarttusie",
//         "password": "!RedVirus!"
//       });
//       if (response != null) {
//         if (jsonDecode(response.body)['status'] == 'successful') {
//           print("login ok !");
//           print("Setting key...");
//           final SetKey =
//               await ApiService.sendRequest({"type": "Set Key", "key": "xxx"});
//           if (SetKey != null) {
//             print(SetKey.body);
//           }
//         }
//       }
//     }
//   }
// }
