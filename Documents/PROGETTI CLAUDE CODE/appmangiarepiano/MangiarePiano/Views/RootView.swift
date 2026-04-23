import SwiftUI

struct RootView: View {
    var body: some View {
        VStack(spacing: 12) {
            Text("MangiarePiano")
                .font(.largeTitle.bold())
            Text("Scaffold OK")
                .foregroundStyle(.secondary)
        }
        .preferredColorScheme(.dark)
    }
}
